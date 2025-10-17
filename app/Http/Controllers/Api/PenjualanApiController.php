<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenjualanApiController extends Controller
{
    protected $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
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
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                // Generate kode penjualan
                $kodePenjualan = $this->generateKodePenjualan();

                // Create header
                $penjualan = \App\Models\Penjualan::create([
                    'kode_penjualan' => $kodePenjualan,
                    'tanggal_penjualan' => now(),
                    'total' => $request->total,
                    'diskon' => 0,
                    'ppn' => 0,
                    'grand_total' => $request->total,
                    'jenis_pembayaran' => $request->metode,
                    'dibayar' => $request->pembayaran,
                    'kembalian' => max(0, $request->pembayaran - $request->total),
                    'status' => 'selesai',
                    'created_by' => auth()->id() ?? 1,
                    'updated_by' => auth()->id() ?? 1
                ]);

                // Process items and update stock
                foreach ($request->items as $item) {
                    \App\Models\PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'barang_id' => $item['barang_id'],
                        'satuan_id' => $item['satuan_id'],
                        'qty' => $item['qty'],
                        'harga_jual' => $item['harga'],
                        'subtotal' => $item['total'],
                        'created_by' => auth()->id() ?? 1,
                        'updated_by' => auth()->id() ?? 1
                    ]);

                    // Convert to base unit and decrease stock
                    $qtyDasar = $this->stokService->convertToDasar(
                        $item['barang_id'],
                        $item['satuan_id'],
                        $item['qty']
                    );

                    $this->stokService->decreaseStock(
                        $item['barang_id'],
                        $qtyDasar,
                        [
                            'source' => 'penjualan_api',
                            'ref' => $penjualan->id,
                            'detail_id' => $item['barang_id']
                        ]
                    );
                }

                // Create payment record
                \App\Models\PenjualanPembayaran::create([
                    'penjualan_id' => $penjualan->id,
                    'metode' => $request->metode,
                    'nominal' => $request->pembayaran,
                    'keterangan' => 'Pembayaran via API',
                    'created_by' => auth()->id() ?? 1,
                    'updated_by' => auth()->id() ?? 1
                ]);
            });

            // Get updated stock for the first item (as example)
            $firstItem = $request->items[0];
            $barang = Barang::find($firstItem['barang_id']);
            $stokTerbaru = $barang->stok;

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil',
                'stok_terbaru' => $stokTerbaru
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique kode penjualan
     */
    private function generateKodePenjualan(): string
    {
        $date = now()->format('Ymd');
        $lastSale = \App\Models\Penjualan::where('kode_penjualan', 'like', "PJ-{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->kode_penjualan, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PJ-{$date}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
