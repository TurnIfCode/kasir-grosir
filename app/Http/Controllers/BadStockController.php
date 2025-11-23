<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use App\Models\BarangPenggantiSales;
use App\Models\KompensasiSales;
use App\Models\Barang;
use App\Models\Supplier;
use App\Services\StokService;
use App\Services\BarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BadStockController extends Controller
{
    protected $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    /**
     * Display the bad stock management page
     */
    public function index()
    {
        return view('bad-stock.index');
    }

    /**
     * Get satuan konversi for a barang
     */
    public function satuanKonversi($barangId)
    {
        $barang = Barang::with('konversiSatuan.satuanKonversi')->find($barangId);

        if (!$barang) {
            return response()->json(['success' => false, 'message' => 'Barang not found'], 404);
        }

        $satuanKonversi = $barang->konversiSatuan->map(function ($konversi) {
            return [
                'id' => $konversi->satuanKonversi->id,
                'nama' => $konversi->satuanKonversi->nama_satuan
            ];
        });

        return response()->json(['success' => true, 'data' => $satuanKonversi]);
    }

    /**
     * Mutasi stok dari GS ke BS
     */
    public function mutasiStok(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|exists:barang,id',
            'satuan_id' => 'required|exists:satuan,id',
            'qty' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request) {
            // Convert qty to base unit
            $qtyDasar = $this->stokService->convertToDasar($request->barang_id, $request->satuan_id, $request->qty);

            // Kurangi stok GS
            $this->stokService->decreaseStock($request->barang_id, $qtyDasar, [
                'action' => 'mutasi_ke_bs',
                'satuan_id' => $request->satuan_id,
                'qty_input' => $request->qty,
                'keterangan' => $request->keterangan
            ]);

            // Catat mutasi
            MutasiStok::create([
                'barang_id' => $request->barang_id,
                'satuan_id' => $request->satuan_id,
                'qty' => $request->qty,
                'qty_dasar' => $qtyDasar,
                'dari_gudang' => 'GS',
                'ke_gudang' => 'BS',
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Mutasi stok berhasil'
        ]);
    }

    /**
     * Barang pengganti dari sales
     */
    public function barangPengganti(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'barang_id' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request) {
            // Tambah stok GS
            $this->stokService->increaseStock($request->barang_id, $request->qty, [
                'action' => 'barang_pengganti',
                'supplier_id' => $request->supplier_id
            ]);

            // Catat barang pengganti
            BarangPenggantiSales::create([
                'supplier_id' => $request->supplier_id,
                'barang_id' => $request->barang_id,
                'qty' => $request->qty,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Barang pengganti berhasil dicatat'
        ]);
    }

    /**
     * Kompensasi sales (potongan pembelian)
     */
    public function kompensasiSales(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'jumlah_kompensasi' => 'required|numeric|min:0.01',
            'barang_id' => 'nullable|exists:barang,id',
            'satuan_id' => 'nullable|exists:satuan,id',
            'qty_rusak' => 'nullable|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If barang_id is provided, satuan_id must also be provided
        if ($request->barang_id && !$request->satuan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Satuan harus dipilih jika barang rusak dipilih',
                'errors' => ['satuan_id' => ['Satuan harus dipilih jika barang rusak dipilih']]
            ], 422);
        }

        $qtyDasar = null;
        if ($request->barang_id && $request->satuan_id && $request->qty_rusak) {
            $qtyDasar = $this->stokService->convertToDasar($request->barang_id, $request->satuan_id, $request->qty_rusak);
        }

        KompensasiSales::create([
            'supplier_id' => $request->supplier_id,
            'jumlah_kompensasi' => $request->jumlah_kompensasi,
            'barang_id' => $request->barang_id,
            'satuan_id' => $request->satuan_id,
            'qty_rusak' => $request->qty_rusak,
            'qty_rusak_dasar' => $qtyDasar,
            'status' => 'pending',
            'keterangan' => $request->keterangan,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kompensasi sales berhasil dicatat'
        ]);
    }

    /**
     * Gunakan kompensasi saat pembelian
     */
    public function gunakanKompensasi($id)
    {
        $kompensasi = KompensasiSales::findOrFail($id);

        if ($kompensasi->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Kompensasi sudah digunakan'
            ], 400);
        }

        $kompensasi->update(['status' => 'digunakan']);

        return response()->json([
            'success' => true,
            'message' => 'Kompensasi berhasil digunakan'
        ]);
    }

    // Data methods untuk API
    public function dataMutasiStok()
    {
        $mutasi = MutasiStok::with(['barang', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $mutasi
        ]);
    }

    public function dataBarangPengganti()
    {
        $pengganti = BarangPenggantiSales::with(['supplier', 'barang', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $pengganti
        ]);
    }

    public function dataKompensasi()
    {
        $kompensasi = KompensasiSales::with(['supplier', 'barang', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $kompensasi
        ]);
    }
}
