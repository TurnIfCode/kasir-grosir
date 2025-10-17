<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanPembayaran;
use Illuminate\Support\Facades\DB;

class PenjualanService
{
    protected $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    /**
     * Create a new sale transaction
     * Handles header, details, payments, and stock updates
     */
    public function createSale(array $header, array $details, array $payments = []): Penjualan
    {
        return DB::transaction(function () use ($header, $details, $payments) {
            // Generate kode penjualan
            $kodePenjualan = $this->generateKodePenjualan();

            // Calculate totals
            $subtotal = 0;
            foreach ($details as $detail) {
                $subtotal += $detail['qty'] * $detail['harga_jual'];
            }

            $diskon = $header['diskon'] ?? 0;
            $ppn = $header['ppn'] ?? 0;
            $total = $subtotal - $diskon;
            $grandTotal = $total + $ppn;

            // Create header
            $penjualan = Penjualan::create([
                'kode_penjualan' => $kodePenjualan,
                'tanggal_penjualan' => $header['tanggal_penjualan'],
                'pelanggan_id' => $header['pelanggan_id'] ?? null,
                'total' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
                'jenis_pembayaran' => $header['jenis_pembayaran'],
                'dibayar' => $header['dibayar'] ?? 0,
                'kembalian' => $this->calculateKembalian($header['jenis_pembayaran'], $header['dibayar'] ?? 0, $grandTotal),
                'catatan' => $header['catatan'] ?? null,
                'status' => 'selesai',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);

            // Create details and update stock
            foreach ($details as $detail) {
                $subtotalDetail = $detail['qty'] * $detail['harga_jual'];

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty' => $detail['qty'],
                    'harga_jual' => $detail['harga_jual'],
                    'subtotal' => $subtotalDetail,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]);

                // Convert to base unit and decrease stock
                $qtyDasar = $this->stokService->convertToDasar(
                    $detail['barang_id'],
                    $detail['satuan_id'],
                    $detail['qty']
                );

                $this->stokService->decreaseStock(
                    $detail['barang_id'],
                    $qtyDasar,
                    [
                        'source' => 'penjualan',
                        'ref' => $penjualan->id,
                        'detail_id' => $detail['barang_id']
                    ]
                );
            }

            // Handle payments
            if ($header['jenis_pembayaran'] === 'tunai') {
                PenjualanPembayaran::create([
                    'penjualan_id' => $penjualan->id,
                    'metode' => 'tunai',
                    'nominal' => $header['dibayar'] ?? 0,
                    'keterangan' => 'Pembayaran tunai',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]);
            } elseif ($header['jenis_pembayaran'] === 'campuran' && !empty($payments)) {
                foreach ($payments as $payment) {
                    PenjualanPembayaran::create([
                        'penjualan_id' => $penjualan->id,
                        'metode' => $payment['metode'],
                        'nominal' => $payment['nominal'],
                        'keterangan' => $payment['keterangan'] ?? null,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);
                }
            }

            return $penjualan;
        });
    }

    /**
     * Rollback stock when deleting a sale
     */
    public function rollbackStockOnDelete(Penjualan $penjualan): void
    {
        DB::transaction(function () use ($penjualan) {
            foreach ($penjualan->details as $detail) {
                $qtyDasar = $this->stokService->convertToDasar(
                    $detail->barang_id,
                    $detail->satuan_id,
                    $detail->qty
                );

                $this->stokService->increaseStock(
                    $detail->barang_id,
                    $qtyDasar,
                    [
                        'source' => 'penjualan_rollback',
                        'ref' => $penjualan->id
                    ]
                );
            }
        });
    }

    /**
     * Generate unique kode penjualan
     */
    private function generateKodePenjualan(): string
    {
        $date = now()->format('Ymd');
        $lastSale = Penjualan::where('kode_penjualan', 'like', "PJ-{$date}%")
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

    /**
     * Calculate kembalian
     */
    private function calculateKembalian(string $jenisPembayaran, float $dibayar, float $grandTotal): float
    {
        if ($jenisPembayaran === 'tunai') {
            return max(0, $dibayar - $grandTotal);
        }
        return 0;
    }
}
