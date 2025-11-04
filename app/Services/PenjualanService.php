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

            // Calculate totals with paket logic
            $subtotal = round($this->calculateSubtotalWithPaket($details));
            $pembulatan = $this->calculatePembulatan($subtotal);

            $diskon = $header['diskon'] ?? 0;
            $ppn = $header['ppn'] ?? 0;
            $total = $subtotal - $diskon;
            $grandTotal = $total + $ppn + $pembulatan;

            // Create header
            $penjualan = Penjualan::create([
                'kode_penjualan' => $kodePenjualan,
                'tanggal_penjualan' => $header['tanggal_penjualan'],
                'pelanggan_id' => $header['pelanggan_id'] ?? null,
                'total' => $subtotal,
                'diskon' => $diskon,
                'ppn' => $ppn,
                'pembulatan' => $pembulatan,
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
     * Calculate subtotal with paket logic
     */
    public function calculateSubtotalWithPaket(array $details): float
    {
        $subtotal = 0;
        $paketTotals = []; // Track total qty per paket

        // First pass: calculate paket totals
        foreach ($details as $detail) {
            $barangId = $detail['barang_id'];
            $pakets = \App\Models\Paket::whereHas('details', function($q) use ($barangId) {
                $q->where('barang_id', $barangId);
            })->with('details')->get();

            if ($pakets->isNotEmpty()) {
                foreach ($pakets as $paket) {
                    if (!isset($paketTotals[$paket->id])) {
                        $paketTotals[$paket->id] = [
                            'paket' => $paket,
                            'total_qty' => 0,
                            'barang_ids' => $paket->details->pluck('barang_id')->toArray()
                        ];
                    }
                    $paketTotals[$paket->id]['total_qty'] += $detail['qty'];
                }
            }
        }

        // Find max harga_per_3 for mixed paket transactions
        $maxHargaPer3 = 0;
        $paketIdsInTransaction = [];
        foreach ($paketTotals as $paketData) {
            if ($paketData['total_qty'] > 0) {
                $paketIdsInTransaction[] = $paketData['paket']->id;
                $maxHargaPer3 = max($maxHargaPer3, $paketData['paket']->harga_per_3);
            }
        }

        // Second pass: calculate subtotals
        foreach ($details as $detail) {
            $barangId = $detail['barang_id'];
            $qty = $detail['qty'];
            $harga = $detail['harga_jual'];
            $tipeHarga = $detail['tipe_harga'];

            // Check if barang is in paket
            $paketFound = null;
            foreach ($paketTotals as $paketData) {
                if (in_array($barangId, $paketData['barang_ids'])) {
                    $paketFound = $paketData;
                    break;
                }
            }

            if ($paketFound) {
                // Paket logic for MINUMAN
                $totalQtyPaket = $paketFound['total_qty'];

                // Jika ada varian campuran, gunakan harga_per_3 tertinggi
                if (count($paketIdsInTransaction) > 1) {
                    if ($totalQtyPaket >= 3) {
                        $hargaPaket = $maxHargaPer3 / 3;
                    } else {
                        $hargaPaket = $paketFound['paket']->harga_per_unit;
                    }
                } else {
                    // Single paket
                    if ($totalQtyPaket >= 3) {
                        $hargaPaket = $paketFound['paket']->harga_per_3 / 3;
                    } else {
                        $hargaPaket = $paketFound['paket']->harga_per_unit;
                    }
                }

                $subtotal += $qty * $hargaPaket;
            } else {
                // Non-paket logic
                $barang = \App\Models\Barang::find($barangId);
                if ($barang && $barang->kategori_id == 1 && $tipeHarga === 'grosir') {
                    // ROKOK grosir logic
                    if ($qty <= 4) {
                        $subtotal += ($harga * $qty) + 500;
                    } elseif ($qty >= 5) {
                        $subtotal += ($harga * $qty) + 1000;
                    }
                } else {
                    // Normal calculation for MINUMAN non-paket or other categories
                    $subtotal += $harga * $qty;
                }
            }
        }

        return $subtotal;
    }

    /**
     * Calculate pembulatan sesuai aturan:
     * remainder = subtotal % 500
     * if remainder == 0: pembulatan = 0
     * else if remainder <= 100: pembulatan = -remainder  // turun ke kelipatan 500 sebelumnya
     * else: pembulatan = (500 - remainder)  // naik ke kelipatan 500 berikutnya
     */
    public function calculatePembulatan(float $subtotal): float
    {
        $remainder = $subtotal % 500;
        if ($remainder == 0) {
            return 0;
        } elseif ($remainder <= 100) {
            return -$remainder;
        } else {
            return 500 - $remainder;
        }
    }

    /**
     * Calculate subtotal and pembulatan together
     */
    public function calculateSubtotalAndPembulatan(array $details): array
    {
        // If no details provided, return zeros
        if (empty($details)) {
            return [
                'subtotal' => 0,
                'pembulatan' => 0,
                'grand_total' => 0
            ];
        }

        $subtotal = round($this->calculateSubtotalWithPaket($details));
        $pembulatan = $this->calculatePembulatan($subtotal);
        $grandTotal = $subtotal + $pembulatan;

        return [
            'subtotal' => $subtotal,
            'pembulatan' => $pembulatan,
            'grand_total' => $grandTotal
        ];
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
