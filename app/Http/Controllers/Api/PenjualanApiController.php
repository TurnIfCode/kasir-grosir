<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\StokService;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenjualanApiController extends Controller
{
    protected $stokService;
    protected $penjualanService;

    public function __construct(StokService $stokService, PenjualanService $penjualanService)
    {
        $this->stokService = $stokService;
        $this->penjualanService = $penjualanService;
    }


    /**
     * API endpoint for creating sales transaction
     * POST /api/penjualan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
            'pembayaran' => 'required|numeric|min:0',
            'metode' => 'required|in:tunai,transfer,qris,debit,kredit',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.satuan_id' => 'required|exists:satuan,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Prepare header data for PenjualanService
            $header = [
                'tanggal_penjualan' => now()->format('Y-m-d'),
                'pelanggan_id' => null, // API doesn't handle customer selection yet
                'diskon' => 0,
                'ppn' => 0,
                'jenis_pembayaran' => $request->metode,
                'dibayar' => $request->pembayaran,
                'catatan' => 'Created via API'
            ];

            // Prepare details data for PenjualanService
            $details = [];
            foreach ($request->items as $item) {
                $details[] = [
                    'barang_id' => $item['barang_id'],
                    'satuan_id' => $item['satuan_id'],
                    'tipe_harga' => 'ecer', // Default, will be overridden by paket logic
                    'qty' => $item['qty'],
                    'harga_jual' => $item['harga'] // Initial price, will be updated by paket logic
                ];
            }

            // Use PenjualanService to handle all logic including paket calculation
            $penjualan = $this->penjualanService->createSale($header, $details);

            // Get updated stock for the first item (as example)
            $firstItem = $request->items[0];
            $barang = Barang::find($firstItem['barang_id']);
            $stokTerbaru = $barang->stok;

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'stok_terbaru' => $stokTerbaru,
                'grand_total' => $penjualan->grand_total,
                'kembalian' => $penjualan->kembalian,
                'pembulatan' => $penjualan->pembulatan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


}
