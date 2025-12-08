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

            // Update harga_jual for paket if applicable
            $details = $this->updateHargaJualForPaket($details);

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
                // Get harga_beli from barang table
                $barang = \App\Models\Barang::find($detail['barang_id']);
                $hargaBeli = $barang ? $barang->harga_beli : 0;

                $subtotalDetail = $this->calculateNormalPrice($detail);

                // Calculate qty_konversi
                $konversi = \App\Models\KonversiSatuan::where('barang_id', $detail['barang_id'])
                    ->where('satuan_konversi_id', $detail['satuan_id'])
                    ->where('status', 'aktif')
                    ->first();

                $qtyKonversi = $konversi ? $detail['qty'] * $konversi->nilai_konversi : $detail['qty'];

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty' => $detail['qty'],
                    'qty_konversi' => $qtyKonversi,
                    'harga_jual' => $detail['harga_jual'],
                    'harga_beli' => $hargaBeli,
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
    // Helper method to get harga from harga_barang by barang_id, satuan_id, tipe_harga
    private function getHargaBarang(int $barangId, int $satuanId, string $tipeHarga): float
    {
        $hargaBarang = \App\Models\HargaBarang::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->where('tipe_harga', $tipeHarga)
            ->first();

        return $hargaBarang ? (float)$hargaBarang->harga : 0;
    }

    public function calculateSubtotalWithPaket(array $details): float
    {
        $subtotal = 0;

        foreach ($details as $detail) {
            $subtotal += $this->calculateNormalPrice($detail);
        }

        return $subtotal;
    }

    /**
     * Calculate normal price for a detail item
     */
    private function calculateNormalPrice(array $detail): float
    {
        $barangId = $detail['barang_id'];
        $satuanId = $detail['satuan_id'];
        $qty = $detail['qty'];
        $harga = $detail['harga_jual'];
        $tipeHarga = $detail['tipe_harga'];

        $subtotal = $harga * $qty;

        // Add surcharge for 'legal' jenis only if tipe_harga is 'grosir' and satuan is 'bungkus' (satuan_id == 2)
        $barang = \App\Models\Barang::find($barangId);
        if ($barang && $barang->jenis && strtolower($barang->jenis) === 'legal' && $tipeHarga === 'grosir' && $satuanId == 2) {
            $surcharge = 0;
            if ($qty >= 1 && $qty <= 4) {
                $surcharge = 500;
            } else if ($qty >= 5) {
                $surcharge = 1000;
            }
            $total = $subtotal + $surcharge;

            // Apply pembulatan sesuai aturan
            $remainder = $total % 1000;
            if ($remainder == 0) {
                $pembulatan = 0;
            } elseif ($remainder >= 1 && $remainder <= 499) {
                $pembulatan = 500 - $remainder;
            } else if ($remainder >= 501 && $remainder <= 999) {
                $pembulatan = 1000 - $remainder;
            } else {
                $pembulatan = 0;
            }
            $subtotal = $total + $pembulatan;
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
            return - $remainder;
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

    /**
     * Update harga_jual in details based on paket pricing rules
     */
    private function updateHargaJualForPaket(array $details): array
    {
        // Get all active pakets with details
        $pakets = \App\Models\Paket::with('details')->where('status', 'aktif')->get();

        // Group details by paket_id for applicable pakets
        $paketAssignments = [];

        foreach ($pakets as $paket) {
            $paketBarangIds = $paket->details->pluck('barang_id')->toArray();
            $paketDetails = collect($details)->whereIn('barang_id', $paketBarangIds);
            $totalQty = $paketDetails->sum('qty');

            if ($totalQty >= $paket->total_qty) {
                // Paket applies, assign to details
                foreach ($details as $index => $detail) {
                    if (in_array($detail['barang_id'], $paketBarangIds)) {
                        if (!isset($paketAssignments[$index]) || $paket->harga > $paketAssignments[$index]['paket']->harga) {
                            $paketAssignments[$index] = [
                                'paket' => $paket,
                                'harga_jual' => $paket->harga / $paket->total_qty
                            ];
                        }
                    }
                }
            }
        }

        // Apply the assignments
        foreach ($paketAssignments as $index => $assignment) {
            $details[$index]['harga_jual'] = $assignment['harga_jual'];
        }

        return $details;
    }

    /**
     * Determine which paket applies based on purchased items
     * Returns paket details if applicable, null otherwise
     */
    public function determinePaket(array $details): ?array
    {
        // Get active pakets with details, sorted by harga ascending (lowest harga first)
        $pakets = \App\Models\Paket::with('details')->where('status', 'aktif')->orderBy('harga')->get();

        if ($pakets->count() < 2) {
            return null; // Need at least two pakets
        }

        $topIcePaket = $pakets->first(); // Lowest harga: paket top ice
        $campurPaket = $pakets->last(); // Highest harga: paket campur

        // Get all unique barang_ids from details
        $barangIds = collect($details)->pluck('barang_id')->unique()->toArray();

        // Check if all barang_ids are in top ice paket details
        $topIceBarangIds = $topIcePaket->details->pluck('barang_id')->toArray();
        $isAllTopIce = collect($barangIds)->every(function ($barangId) use ($topIceBarangIds) {
            return in_array($barangId, $topIceBarangIds);
        });

        // Select paket: top ice if all items match, else campur
        $selectedPaket = $isAllTopIce ? $topIcePaket : $campurPaket;

        // Get barang_ids from selected paket
        $selectedPaketBarangIds = $selectedPaket->details->pluck('barang_id')->toArray();

        // Calculate matching barang_ids: intersection of transaction barang_ids and paket barang_ids
        $matchingBarangIds = array_intersect($barangIds, $selectedPaketBarangIds);

        // Paket valid only if number of distinct matching barang_ids == total_qty
        if (count($matchingBarangIds) != $selectedPaket->total_qty) {
            return null; // Paket not valid
        }

        // Calculate matching qty: sum qty of items that are in selected paket
        $matchingQty = collect($details)->whereIn('barang_id', $selectedPaketBarangIds)->sum('qty');

        // Calculate jumlah paket (floor division)
        $jumlahPaket = floor($matchingQty / $selectedPaket->total_qty);

        if ($jumlahPaket == 0) {
            return null; // No paket applies
        }

        // Calculate prices
        $hargaPerPaket = $selectedPaket->harga;
        $hargaSatuan = round($hargaPerPaket / $selectedPaket->total_qty, 2);
        $totalHargaPaket = $jumlahPaket * $hargaPerPaket;

        return [
            'jumlah_paket' => $jumlahPaket,
            'harga_per_paket' => $hargaPerPaket,
            'harga_satuan' => $hargaSatuan,
            'total_harga_paket' => $totalHargaPaket
        ];
    }
}
