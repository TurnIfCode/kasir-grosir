<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembelianPembayaran;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use App\Models\KasSaldoTransaksi;
use App\Models\KasSaldo;
use App\Models\Kas;
use App\Models\Log;
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
        $kasSaldo = KasSaldo::all();
        return view('transaksi.pembelian.create', compact('kasSaldo'));
    }

    public function store(Request $request)
    {
        // Handle details from JSON string if sent from list
        if ($request->has('details') && is_string($request->details)) {
            $request->merge(['details' => json_decode($request->details, true)]);
        }

        // Handle pembayaran from JSON string if sent from list
        if ($request->has('pembayaran') && is_string($request->pembayaran)) {
            $request->merge(['pembayaran' => json_decode($request->pembayaran, true)]);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal_pembelian' => 'required|date',
            'catatan' => 'nullable|string',
            'diskon' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,selesai',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barang,id',
            'details.*.satuan_id' => 'required|exists:satuan,id',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.harga_beli' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string',
            'pembayaran' => 'nullable|array',
            'pembayaran.*.metode' => 'required|in:tunai,transfer',
            'pembayaran.*.nominal' => 'required|numeric|min:0.01',
            'pembayaran.*.keterangan' => 'nullable|string'
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
            $status = $request->status ?? 'draft';

            // Simpan header pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'supplier_id' => $request->supplier_id,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'total' => $total,
                'status' => $status,
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);

            // Simpan details
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
            }

            // Jika status selesai, update stok dan harga barang
            if ($pembelian->status === 'selesai') {
                foreach ($request->details as $detail) {
                    $this->updateStokDanHargaBarang($detail['barang_id'], $detail['satuan_id'], $detail['qty'], $detail['harga_beli']);
                }
            }

            // Simpan pembayaran jika ada
            if ($request->has('pembayaran') && is_array($request->pembayaran)) {
                foreach ($request->pembayaran as $pembayaranData) {
                    $pembayaran = PembelianPembayaran::create([
                        'pembelian_id' => $pembelian->id,
                        'metode' => $pembayaranData['metode'],
                        'nominal' => $pembayaranData['nominal'],
                        'keterangan' => $pembayaranData['keterangan'] ?? null,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);

                    $nominalPembayaran = round($pembayaran->nominal,2);
                    $bankKas = $pembayaran->keterangan;

                    // Potong saldo kas hanya untuk metode transfer saat pembelian selesai
                    if ($pembayaran->metode === 'transfer') {
                        $this->potongSaldoKas($nominalPembayaran, $bankKas);
                    }

                    // Log pembayaran
                    Log::create([
                        'keterangan' => 'Menambah pembayaran pembelian dengan kode ' . $pembelian->kode_pembelian . ' menggunakan metode ' . $pembayaran->metode . ' sebesar Rp ' . number_format($pembayaran->nominal, 0, ',', '.') . ($pembayaran->keterangan ? ' dengan keterangan: ' . $pembayaran->keterangan : ''),
                        'created_by' => auth()->id(),
                        'created_at' => now()
                    ]);
                }
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

    private function updateStokDanHargaBarang($barangId, $satuanId, $qty, $hargaBeli)
    {
        $konversi_pembelian = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->first();

        $barang = Barang::find($barangId);
        $nilai_konversi = $konversi_pembelian ? $konversi_pembelian->nilai_konversi : 1;
        $harga_beli_dasar = $hargaBeli / $nilai_konversi;

        // Update harga dasar dan stok
        $barang->update([
            'stok' => $barang->stok + ($qty * $nilai_konversi),
            'harga_beli' => $harga_beli_dasar,
            'updated_by' => auth()->id()
        ]);

        // Update semua konversi satuan yang berkaitan
        $semua_konversi = KonversiSatuan::where('barang_id', $barangId)->get();
        foreach ($semua_konversi as $k) {
            $harga_konversi = $harga_beli_dasar * $k->nilai_konversi;
            $k->update([
                'harga_beli' => $harga_konversi,
                'updated_by' => auth()->id()
            ]);
        }
    }

    private function kembalikanStokBarang($barangId, $satuanId, $qty)
    {
        $konversi_pembelian = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->first();

        $nilai_konversi = $konversi_pembelian ? $konversi_pembelian->nilai_konversi : 1;
        $stokDasar = $qty * $nilai_konversi;

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
                       ($row->status === 'draft' ? '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>' : '');
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
            'status' => 'nullable|in:draft,selesai',
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
            // Jika status lama adalah draft dan akan diubah ke selesai, kembalikan stok lama terlebih dahulu
            if ($pembelian->status === 'draft' && $request->status === 'selesai') {
                // Stok belum terupdate, langsung update dengan data baru
            } elseif ($pembelian->status === 'draft' && $request->status === 'draft') {
                // Tetap draft, tidak perlu kembalikan stok
            }

            // Hitung subtotal baru
            $subtotal = 0;
            foreach ($request->details as $detail) {
                $subtotal += $detail['qty'] * $detail['harga_beli'];
            }

            $diskon = $request->diskon ?? 0;
            $ppn = $request->ppn ?? 0;
            $total = $subtotal - $diskon + $ppn;
            $status = $request->status ?? $pembelian->status;

            // Update header pembelian
            $pembelian->update([
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'supplier_id' => $request->supplier_id,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'total' => $total,
                'status' => $status,
                'catatan' => $request->catatan,
                'updated_by' => auth()->id()
            ]);

            // Hapus detail lama
            $pembelian->details()->delete();

            // Simpan details baru
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
            }

            // Jika status baru adalah selesai, update stok dan harga barang
            if ($status === 'selesai') {
                foreach ($request->details as $detail) {
                    $this->updateStokDanHargaBarang($detail['barang_id'], $detail['satuan_id'], $detail['qty'], $detail['harga_beli']);
                }
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
                // Untuk status selesai, update stok dan harga barang
                foreach ($pembelian->details as $detail) {
                    $this->updateStokDanHargaBarang($detail->barang_id, $detail->satuan_id, $detail->qty, $detail->harga_beli);
                }
            } elseif ($newStatus === 'batal') {
                // Untuk status batal, tidak perlu melakukan apa-apa karena stok belum terupdate
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

    private function potongSaldoKas($nominal, $bankKas)
    {
        // Ambil saldo kas utama (asumsi sumber_kas = 'utama' atau yang pertama)
        $kasSaldo = KasSaldo::where('kas', $bankKas)->first(); // Atau bisa disesuaikan dengan logika bisnis
        $kasSaldoId = $kasSaldo->id;
        $kasSaldoAwal = $kasSaldo->saldo;
        $kasSaldoAwal = round($kasSaldoAwal, 2);

        $keteranganKas = 'Pengurangan saldo kas sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' untuk pembayaran pembelian via transfer dari kas ' . $bankKas;
        
        $kasSaldoAkhir = $kasSaldoAwal - $nominal;
        $kasSaldoAkhir = round($kasSaldoAkhir, 2);

        // disini insert kas saldo transaksi
        $kasSaldoTransaksi = new KasSaldoTransaksi();
        $kasSaldoTransaksi->kas_saldo_id = $kasSaldoId;
        $kasSaldoTransaksi->tipe = 'keluar';
        $kasSaldoTransaksi->saldo_awal = $kasSaldoAwal;
        $kasSaldoTransaksi->saldo_akhir = $kasSaldoAkhir;
        $kasSaldoTransaksi->keterangan = $keteranganKas;
        $kasSaldoTransaksi->created_by = auth()->id();
        $kasSaldoTransaksi->created_at = now();
        $kasSaldoTransaksi->save();

        //disini insert ke kas
        $kas = new Kas();
        $kas->tanggal = now();
        $kas->tipe = 'keluar';
        $kas->sumber_kas = $bankKas;
        $kas->kategori = 'Pembelian';
        $kas->keterangan = $keteranganKas;
        $kas->nominal = $nominal;
        $kas->user_id = auth()->id();
        $kas->created_by = auth()->id();
        $kas->created_at = now();
        $kas->updated_by = auth()->id();
        $kas->updated_at = now();
        $kas->save();

        // insert ke log
        Log::create([
            'keterangan' => $keteranganKas,
            'created_by' => auth()->id(),
            'created_at' => now()
        ]);

    }

    /**
     * Hitung harga konversi satuan berdasarkan harga beli satuan pembelian tertinggi
     *
     * @param int $barang_id
     * @param float $harga_beli
     * @param int $satuan_pembelian
     * @return void
     */
    public function hitungKonversiHarga($barang_id, $harga_beli, $satuan_pembelian)
    {
        // Ambil semua konversi satuan aktif untuk barang ini
        $konversiSatuans = KonversiSatuan::where('barang_id', $barang_id)
            ->where('status', 'aktif')
            ->get()
            ->keyBy('satuan_dasar_id'); // Key by satuan_dasar_id untuk pencarian cepat

        $currentSatuan = $satuan_pembelian;
        $currentHarga = $harga_beli;

        // Loop untuk menghitung harga konversi ke bawah
        while (isset($konversiSatuans[$currentSatuan])) {
            $konversi = $konversiSatuans[$currentSatuan];

            // Hitung harga beli untuk satuan konversi (biarkan sebagai decimal)
            $hargaBeliKonversi = $currentHarga / $konversi->nilai_konversi;

            // Hitung harga jual dengan margin 5%
            $hargaJualKonversi = round($hargaBeliKonversi * 1.05);

            // Update record konversi
            $konversi->update([
                'harga_beli' => $hargaBeliKonversi,
                'harga_jual' => $hargaJualKonversi,
                'updated_at' => now()
            ]);

            // Pindah ke satuan berikutnya
            $currentSatuan = $konversi->satuan_konversi_id;
            $currentHarga = $hargaBeliKonversi;
        }
    }
}
