<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use App\Services\BarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    protected $barangService;

    public function __construct(BarangService $barangService)
    {
        $this->barangService = $barangService;
    }
    public function index()
    {
        return view('transaksi.pembelian.index');
    }

    public function create()
    {
        return view('transaksi.pembelian.create');
    }

    public function store(Request $request)
    {
        // Handle details from JSON string if sent from list
        if ($request->has('details') && is_string($request->details)) {
            $request->merge(['details' => json_decode($request->details, true)]);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal_pembelian' => 'required|date',
            'catatan' => 'nullable|string',
            'diskon' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.satuan_id' => 'required|exists:satuan,id',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.harga_beli' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate kode pembelian
            $kodePembelian = $this->generateKodePembelian();

            // Hitung subtotal dari details
            $subtotal = 0;
            foreach ($request->details as $detail) {
                $subtotal += $detail['qty'] * $detail['harga_beli'];
            }

            $diskon = $request->diskon ?? 0;
            $ppn = $request->ppn ?? 0;
            $total = $subtotal - $diskon + $ppn;

            // Simpan header pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'supplier_id' => $request->supplier_id,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'total' => $total,
                'status' => 'draft',
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);

            // Simpan details dan update stok
            foreach ($request->details as $detail) {
                $subtotalDetail = $detail['qty'] * $detail['harga_beli'];

                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty' => $detail['qty'],
                    'harga_beli' => $detail['harga_beli'],
                    'subtotal' => $subtotalDetail,
                    'keterangan' => $detail['keterangan'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]);

                // Update stok barang
                $this->updateStokBarang($detail['barang_id'], $detail['satuan_id'], $detail['qty']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembelian berhasil disimpan',
                'data' => $pembelian
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'details.barang', 'details.satuan'])->findOrFail($id);
        return view('transaksi.pembelian.show', compact('pembelian'));
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Jika status sudah selesai, tidak bisa dihapus
        if ($pembelian->status === 'selesai') {
            return response()->json([
                'status' => false,
                'message' => 'Pembelian yang sudah selesai tidak dapat dihapus'
            ]);
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok barang
            foreach ($pembelian->details as $detail) {
                $this->kembalikanStokBarang($detail->barang_id, $detail->satuan_id, $detail->qty);
            }

            $pembelian->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembelian berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateKodePembelian()
    {
        $tanggal = now()->format('Ymd');
        $lastPembelian = Pembelian::where('kode_pembelian', 'like', 'PB-' . $tanggal . '%')
            ->orderBy('kode_pembelian', 'desc')
            ->first();

        if ($lastPembelian) {
            $lastNumber = intval(substr($lastPembelian->kode_pembelian, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PB-' . $tanggal . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    private function updateStokBarang($barangId, $satuanId, $qty)
    {
        // Cari konversi satuan untuk mendapatkan nilai konversi ke satuan dasar
        $konversi = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->where('status', 'aktif')
            ->first();

        if ($konversi) {
            // Jika ada konversi, hitung stok berdasarkan nilai konversi
            $stokDasar = $qty * $konversi->nilai_konversi;
        } else {
            // Jika tidak ada konversi, asumsikan satuan sudah dasar
            $stokDasar = $qty;
        }

        // Update stok barang
        $barang = Barang::findOrFail($barangId);
        $barang->increment('stok', $stokDasar);
        $barang->updated_by = auth()->id();
        $barang->save();
    }

    private function kembalikanStokBarang($barangId, $satuanId, $qty)
    {
        // Cari konversi satuan untuk mendapatkan nilai konversi ke satuan dasar
        $konversi = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->where('status', 'aktif')
            ->first();

        if ($konversi) {
            // Jika ada konversi, hitung stok berdasarkan nilai konversi
            $stokDasar = $qty * $konversi->nilai_konversi;
        } else {
            // Jika tidak ada konversi, asumsikan satuan sudah dasar
            $stokDasar = $qty;
        }

        // Kurangi stok barang
        $barang = Barang::findOrFail($barangId);
        $barang->decrement('stok', $stokDasar);
        $barang->updated_by = auth()->id();
        $barang->save();
    }

    public function autocompleteBarang(Request $request)
    {
        $query = $request->get('q', '');

        $barangs = Barang::where('status', 'AKTIF')
            ->where(function ($q) use ($query) {
                $q->where('nama_barang', 'like', '%' . $query . '%')
                  ->orWhere('kode_barang', 'like', '%' . $query . '%')
                  ->orWhere('barcode', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get();

        $results = $barangs->map(function ($barang) {
            return [
                'id' => $barang->id,
                'label' => $barang->kode_barang . ' - ' . $barang->nama_barang . ($barang->barcode ? ' (' . $barang->barcode . ')' : ''),
                'value' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'barcode' => $barang->barcode
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }

    public function data(Request $request)
    {
        $query = Pembelian::with(['supplier']);

        // Filter berdasarkan parameter
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            $query->where('tanggal_pembelian', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            $query->where('tanggal_pembelian', '<=', $request->tanggal_sampai);
        }

        return \DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tanggal_pembelian_formatted', function ($row) {
                return $row->tanggal_pembelian->format('d/m/Y');
            })
            ->addColumn('supplier_nama', function ($row) {
                return $row->supplier->nama_supplier ?? '-';
            })
            ->addColumn('total_formatted', function ($row) {
                return 'Rp ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('pembelian.show', $row->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a> ' .
                       ($row->status === 'draft' ? '<a href="' . route('pembelian.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a> ' .
                       '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>' : '');
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with(['details.barang', 'details.satuan'])->findOrFail($id);

        // Cek jika status sudah selesai, tidak bisa diedit
        if ($pembelian->status === 'selesai') {
            return redirect()->route('pembelian.index')->with('error', 'Pembelian yang sudah selesai tidak dapat diedit');
        }

        $suppliers = Supplier::where('status', 'AKTIF')->get();
        $barangs = Barang::where('status', 'AKTIF')->get();
        $satuans = Satuan::where('status', 'AKTIF')->get();

        return view('transaksi.pembelian.edit', compact('pembelian', 'suppliers', 'barangs', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Cek jika status sudah selesai, tidak bisa diupdate
        if ($pembelian->status === 'selesai') {
            return response()->json([
                'status' => false,
                'message' => 'Pembelian yang sudah selesai tidak dapat diupdate'
            ]);
        }

        // Handle details from JSON string if sent from list
        if ($request->has('details') && is_string($request->details)) {
            $request->merge(['details' => json_decode($request->details, true)]);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal_pembelian' => 'required|date',
            'catatan' => 'nullable|string',
            'diskon' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.satuan_id' => 'required|exists:satuan,id',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.harga_beli' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok lama
            foreach ($pembelian->details as $detail) {
                $this->kembalikanStokBarang($detail->barang_id, $detail->satuan_id, $detail->qty);
            }

            // Hitung subtotal baru
            $subtotal = 0;
            foreach ($request->details as $detail) {
                $subtotal += $detail['qty'] * $detail['harga_beli'];
            }

            $diskon = $request->diskon ?? 0;
            $ppn = $request->ppn ?? 0;
            $total = $subtotal - $diskon + $ppn;

            // Update header pembelian
            $pembelian->update([
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'supplier_id' => $request->supplier_id,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'total' => $total,
                'catatan' => $request->catatan,
                'updated_by' => auth()->id()
            ]);

            // Hapus detail lama
            $pembelian->details()->delete();

            // Simpan details baru dan update stok
            foreach ($request->details as $detail) {
                $subtotalDetail = $detail['qty'] * $detail['harga_beli'];

                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty' => $detail['qty'],
                    'harga_beli' => $detail['harga_beli'],
                    'subtotal' => $subtotalDetail,
                    'keterangan' => $detail['keterangan'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]);

                // Update stok barang baru
                $this->updateStokBarang($detail['barang_id'], $detail['satuan_id'], $detail['qty']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembelian berhasil diupdate',
                'data' => $pembelian
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Validasi status
        $request->validate([
            'status' => 'required|in:selesai,batal'
        ]);

        $newStatus = $request->status;

        // Cek jika status sudah selesai atau batal, tidak bisa diubah lagi
        if ($pembelian->status !== 'draft') {
            return response()->json([
                'status' => false,
                'message' => 'Status pembelian sudah tidak dapat diubah'
            ]);
        }

        DB::beginTransaction();
        try {
            if ($newStatus === 'selesai') {
                // Untuk status selesai, pastikan stok sudah terupdate (sudah dilakukan di store)
                // Tidak perlu update stok lagi karena sudah dilakukan saat create
            } elseif ($newStatus === 'batal') {
                // Untuk status batal, kembalikan stok barang
                foreach ($pembelian->details as $detail) {
                    $this->kembalikanStokBarang($detail->barang_id, $detail->satuan_id, $detail->qty);
                }
            }

            // Update status pembelian
            $pembelian->update([
                'status' => $newStatus,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status pembelian berhasil diubah menjadi ' . ($newStatus === 'selesai' ? 'selesai' : 'batal')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
