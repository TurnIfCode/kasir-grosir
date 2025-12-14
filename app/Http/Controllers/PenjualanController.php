<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanPembayaran;
use App\Models\Pelanggan;
use App\Models\Satuan;
use App\Services\BarangService;
use App\Services\HargaService;
use App\Services\StokService;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    protected $barangService;
    protected $hargaService;
    protected $stokService;
    protected $penjualanService;

    public function __construct(
        BarangService $barangService,
        HargaService $hargaService,
        StokService $stokService,
        PenjualanService $penjualanService
    ) {
        $this->barangService = $barangService;
        $this->hargaService = $hargaService;
        $this->stokService = $stokService;
        $this->penjualanService = $penjualanService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('transaksi.penjualan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pelanggans = Pelanggan::where('status', 'aktif')->get();
        $defaultPelanggan = Pelanggan::find(1); // Set default pelanggan with id=1
        return view('penjualan.create', compact('pelanggans', 'defaultPelanggan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_penjualan' => 'required|date',
            'pelanggan_id' => 'nullable|exists:pelanggan,id',
            'catatan' => 'nullable|string',
            'diskon' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:tunai,non_tunai,campuran',
            'dibayar' => 'nullable|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.satuan_id' => 'required|exists:satuan,id',
            'details.*.tipe_harga' => 'required|in:ecer,grosir,reseller',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.harga_jual' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.metode' => 'required|in:tunai,transfer,qris,debit,kredit',
            'payments.*.nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $penjualan = $this->penjualanService->createSale(
                $request->only(['tanggal_penjualan', 'pelanggan_id', 'catatan', 'diskon', 'ppn', 'jenis_pembayaran', 'dibayar']),
                $request->details,
                $request->payments ?? []
            );



            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil disimpan',
                'data' => [
                    'id' => $penjualan->id,
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'subtotal' => $penjualan->total,
                    'pembulatan' => $penjualan->pembulatan,
                    'grand_total' => $penjualan->grand_total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $penjualan = Penjualan::with(['details.barang', 'details.satuan', 'pelanggan', 'pembayarans'])->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Print the specified resource.
     */
    public function print($id)
    {
        $penjualan = Penjualan::with(['details.barang', 'details.satuan', 'pelanggan', 'pembayarans', 'creator'])->findOrFail($id);
        $profilToko = \App\Models\ProfilToko::first();
        return view('penjualan.print', compact('penjualan', 'profilToko'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Implement edit form if needed
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implement update logic if needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            $this->penjualanService->rollbackStockOnDelete($penjualan);
            $penjualan->delete();


            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint for barang autocomplete
     */
    public function autocompleteBarang(Request $request)
    {
        $term = $request->get('term', '');
        $barangs = $this->barangService->search($term);


        return response()->json([
            'success' => true,
            'data' => $barangs
        ]);
    }

    /**
     * AJAX endpoint to get satuan options for a barang
     */
    public function getSatuanByBarang($barangId)
    {
        try {
            $satuans = $this->barangService->getSatuanByBarang($barangId);

            return response()->json([
                'success' => true,
                'data' => $satuans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get available tipe_harga for a barang + satuan
     */
    public function getTipeHargaByBarangSatuan($barangId, $satuanId)
    {
        try {
            $tipeHargas = \App\Models\HargaBarang::where('barang_id', $barangId)
                ->where('satuan_id', $satuanId)
                ->where('status', 'aktif')
                ->pluck('tipe_harga')
                ->unique()
                ->values()
                ->toArray();

            // If no specific harga_barang records, default to ecer
            if (empty($tipeHargas)) {
                $tipeHargas = ['ecer'];
            }


            return response()->json([
                'success' => true,
                'data' => $tipeHargas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga for barang + satuan + tipe
     */
    public function getHargaByBarangSatuan($barangId, $satuanId, Request $request)
    {
        $tipe = $request->get('tipe', 'ecer');
        $pelangganId = $request->get('pelanggan_id');


        try {
            $harga = $this->hargaService->lookupHarga($barangId, $satuanId, $tipe, $pelangganId);
            $harga['harga'] = round($harga['harga']); // Round to integer
            return response()->json([
                'success' => true,
                'data' => $harga
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga for barang + satuan (defaulting to ecer)
     */

    public function getHargaByBarangSatuanDefault($barangId, $satuanId)
    {
        try {
            $harga = $this->hargaService->lookupHarga($barangId, $satuanId, 'ecer');
            return response()->json([
                'success' => true,
                'data' => $harga
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get harga_barang info for a barang
     */
    public function getHargaBarangInfo($barangId)
    {
        try {
            $hargaBarang = \App\Models\HargaBarang::where('barang_id', $barangId)
                ->where('status', 'aktif')
                ->with(['satuan'])
                ->get()
                ->map(function($harga) {
                    return [
                        'satuan_id' => $harga->satuan_id,
                        'nama_satuan' => $harga->satuan->nama_satuan ?? 'Satuan',
                        'tipe_harga' => $harga->tipe_harga,
                        'harga' => $harga->harga
                    ];
                });


            return response()->json([
                'success' => true,
                'data' => $hargaBarang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * AJAX endpoint to get paket data for a barang
     */
    public function getPaketBarang($barangId)
    {
        try {
            // First, try to get paket with jenis 'tidak'
            $pakets = \App\Models\Paket::whereHas('details', function($q) use ($barangId) {
                $q->where('barang_id', $barangId);
            })->with(['details.barang', 'details'])
            ->where('status', 'aktif')
            ->where('jenis', 'tidak')
            ->get();

            // If no results with jenis 'tidak', try with jenis 'campur'
            if ($pakets->isEmpty()) {
                $pakets = \App\Models\Paket::whereHas('details', function($q) use ($barangId) {
                    $q->where('barang_id', $barangId);
                })->with(['details.barang', 'details'])
                ->where('status', 'aktif')
                ->where('jenis', 'campur')
                ->get();
            }

            // If still no results, get all paket without jenis filter
            if ($pakets->isEmpty()) {
                $pakets = \App\Models\Paket::whereHas('details', function($q) use ($barangId) {
                    $q->where('barang_id', $barangId);
                })->with(['details.barang', 'details'])
                ->where('status', 'aktif')
                ->get();
            }

            $data = $pakets->map(function($paket) {
                return [
                    'id' => $paket->id,
                    'nama' => $paket->nama,
                    'total_qty' => $paket->total_qty,
                    'harga' => $paket->harga,
                    'jenis' => $paket->jenis ?? 'tidak_diketahui',
                    'barang_ids' => $paket->details->pluck('barang_id')->toArray(),
                    'barang_nama' => $paket->details->pluck('barang.nama_barang')->toArray(),
                ];
            });


            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to calculate subtotal and pembulatan for current transaction details
     */
    public function calculateSubtotal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'details' => 'nullable|array',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.satuan_id' => 'required|exists:satuan,id',
            'details.*.tipe_harga' => 'required|in:ecer,grosir,reseller',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.harga_jual' => 'required|numeric|min:0',
            'pelanggan_id' => 'nullable|exists:pelanggan,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $details = $request->details ?? [];
            $pelangganId = $request->pelanggan_id;
            $calculation = $this->penjualanService->calculateSubtotalAndPembulatan($details, $pelangganId);


            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Data endpoint for index
     */
    public function data(Request $request)
    {
        $query = Penjualan::with('pelanggan');

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_penjualan', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_penjualan', '<=', $request->tanggal_akhir);
        }

        $data = [];
        foreach ($query->get() as $penjualan) {
            $data[] = [
                'kode_penjualan' => $penjualan->kode_penjualan,
                'tanggal_penjualan' => $penjualan->tanggal_penjualan->format('d/m/Y'),
                'pelanggan' => $penjualan->pelanggan ? $penjualan->pelanggan->nama_pelanggan : '-',
                'grand_total' => 'Rp ' . number_format($penjualan->grand_total, 0, ',', '.'),
                'jenis_pembayaran' => $penjualan->jenis_pembayaran,
                'status' => $penjualan->status,
                'aksi' => '<a href="' . route('penjualan.show', $penjualan->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a>'
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }
}
