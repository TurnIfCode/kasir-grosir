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

            // Create header first (with initial values)
            $penjualan = Penjualan::create([
                'kode_penjualan' => $kodePenjualan,
                'tanggal_penjualan' => $header['tanggal_penjualan'],
                'pelanggan_id' => $header['pelanggan_id'] ?? null,
                'total' => 0, // Will be updated after calculation
                'diskon' => $header['diskon'] ?? 0,
                'ppn' => $header['ppn'] ?? 0,
                'pembulatan' => 0, // Will be updated after calculation
                'grand_total' => 0, // Will be updated after calculation
                'jenis_pembayaran' => $header['jenis_pembayaran'],
                'dibayar' => $header['dibayar'] ?? 0,
                'kembalian' => 0, // Will be updated after calculation
                'catatan' => $header['catatan'] ?? null,
                'status' => 'selesai',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_by' => auth()->id()
            ]);


            $pelangganId = $header['pelanggan_id'] ?? null;
            $subtotalDetails = 0;

            // Apply paket pricing first - update harga_jual for all items based on paket
            $details = $this->updateHargaJualForPaket($details);

            // Create details and update stock
            foreach ($details as $detail) {
                // Get harga_beli from barang table
                $barang = \App\Models\Barang::with('kategori')->find($detail['barang_id']);
                $hargaBeli = $barang ? $barang->harga_beli : 0;


                // Calculate subtotal for this detail using the same logic as calculateNormalPrice
                $subtotalDetail = $this->calculateNormalPrice($detail, $pelangganId, $details);

                // Use harga_jual yang sudah diupdate oleh paket (jangan dihitung ulang)
                $hargaJualDetail = $detail['harga_jual'];

                // Calculate qty_konversi
                $konversi = \App\Models\KonversiSatuan::where('barang_id', $detail['barang_id'])
                    ->where('satuan_konversi_id', $detail['satuan_id'])
                    ->where('status', 'aktif')
                    ->first();

                $qtyKonversi = $konversi ? $detail['qty'] * $konversi->nilai_konversi : $detail['qty'];

                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty' => $detail['qty'],
                    'qty_konversi' => $qtyKonversi,
                    'harga_jual' => $hargaJualDetail,
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

                $subtotalDetails += round($subtotalDetail, 2);
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

            // Calculate final totals using the same logic as calculateSubtotalAndPembulatan
            $diskon = $header['diskon'] ?? 0;
            $ppn = $header['ppn'] ?? 0;
            $subtotalAfterDiskon = $subtotalDetails - $diskon;
            $subtotalAfterDiskon = round($subtotalAfterDiskon, 2);
            $subtotalAfterPpn = $subtotalAfterDiskon + $ppn;
            
            // Calculate pembulatan based on subtotal after diskon and ppn
            $pembulatan = $this->calculatePembulatan($subtotalAfterPpn);
            $grandTotal = $subtotalAfterPpn + $pembulatan;
            $grandTotal = round($grandTotal, 2);
            
            $kembalian = $this->calculateKembalian($header['jenis_pembayaran'], $header['dibayar'] ?? 0, $grandTotal);

            // Update penjualan with calculated values
            $penjualan->total = $subtotalAfterPpn; // Subtotal after diskon and ppn, before pembulatan
            $penjualan->pembulatan = $pembulatan;
            $penjualan->grand_total = $grandTotal;
            $penjualan->kembalian = $kembalian;
            $penjualan->updated_by = auth()->id();
            $penjualan->updated_at = now();
            $penjualan->save();

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

    public function calculateSubtotalWithPaket(array $details, ?int $pelangganId = null): float
    {
        $subtotal = 0;

        foreach ($details as $detail) {
            $subtotal += $this->calculateNormalPrice($detail, $pelangganId);
        }

        return $subtotal;
    }





    /**
     * Calculate normal price for a detail item - FOLLOWING EXACT FRONTEND LOGIC
     * NOTE: This method applies pembulatan per item for non-paket items
     * sesuai dengan logic frontend
     */
    private function calculateNormalPrice(array $detail, ?int $pelangganId = null, array $allDetails = []): float
    {
        $barangId = $detail['barang_id'];
        $satuanId = $detail['satuan_id'];
        $qty = $detail['qty'];
        $harga = $detail['harga_jual'];
        $tipeHarga = $detail['tipe_harga'];

        $barang = \App\Models\Barang::with('kategori')->find($barangId);
        $customerType = $this->getCustomerType($pelangganId);
        


        // Check if item is in active paket - following frontend logic
        $isInActivePaket = $this->isBarangInActivePaket($barangId, $detail, $allDetails);

        // MODAL CUSTOMER: qty * harga_beli
        if ($customerType['type'] === 'modal') {
            $hargaBeli = $barang ? $barang->harga_beli : 0;
            $subtotal = round($qty * $hargaBeli);
            return $subtotal;
        }
        
        // ANTAR CUSTOMER: special pricing
        if ($customerType['type'] === 'antar') {
            $ongkos = $customerType['ongkos'];
            
            // If Rokok with jenis legal, add ongkos to harga
            if ($barang && $barang->kategori && 
                strtolower($barang->kategori->nama_kategori) === 'rokok & tembakau' && 
                $barang->jenis && strtolower($barang->jenis) === 'legal') {
                $subtotal = round($qty * ($harga + $ongkos));
            } else {
                $subtotal = round($qty * $harga);
            }
            return $subtotal;
        }
        
        // KEDAI KOPI & HUBUAN: special handling
        if (in_array($customerType['type'], ['kedai_kopi', 'hubuan'])) {
            if ($customerType['type'] === 'kedai_kopi') {
                // Kedai Kopi: qty * harga_beli
                $hargaBeli = $barang ? $barang->harga_beli : 0;
                return round($qty * $hargaBeli);
            } elseif ($customerType['type'] === 'hubuan') {
                // Hubuan: add 3000 for Legal Rokok
                $subtotal = $harga * $qty;
                if ($barang && $barang->kategori && 
                    strtolower($barang->kategori->nama_kategori) === 'rokok & tembakau' && 
                    $barang->jenis && strtolower($barang->jenis) === 'legal') {
                    $subtotal = round($qty * ($harga + 3000));
                }
                return round($subtotal);
            }
        }
        
        // REGULAR CUSTOMERS: apply all rules
        $subtotal = round($harga * $qty);
        

        // LEGAL GROSIR SURCHARGE
        if ($barang && $barang->jenis && strtolower($barang->jenis) === 'legal' && 
            $tipeHarga === 'grosir' && $satuanId == 2) {
            
            $surcharge = 0;
            if ($qty >= 1 && $qty <= 4) {
                $surcharge = 500;
            } else if ($qty >= 5) {
                $surcharge = 1000;
            }
            $subtotal += $surcharge;
            
            // NO pembulatan per item - let it be calculated at total level
            
            return $subtotal;
        }
        
        // BARANG TIMBANGAN MARKUP
        if ($barang && $barang->kategori && 
            strtolower($barang->kategori->nama_kategori) === 'barang timbangan' && 
            $barang->satuan_id == $satuanId) {
            
            $hasilDasar = $qty * $harga;
            $subtotal = ceil($hasilDasar / 1000) * 1000 + 1000;
        }
        
        // NO pembulatan per item - let it be calculated at total level
        
        return $subtotal;
    }


    /**
     * Calculate subtotal and pembulatan together with paket processing
     * Following exact frontend logic: no additional pembulatan at total level
     * because each item is already rounded individually
     */
    public function calculateSubtotalAndPembulatan(array $details, ?int $pelangganId = null): array
    {
        // If no details provided, return zeros
        if (empty($details)) {
            return [
                'subtotal' => 0,
                'pembulatan' => 0,
                'grand_total' => 0
            ];
        }

        // Apply paket pricing first - this updates harga_jual in details
        $detailsWithPaket = $this->updateHargaJualForPaket($details);



        // Calculate subtotal by summing each detail using calculateNormalPrice
        // calculateNormalPrice now applies pembulatan per item (following frontend logic)
        $subtotal = 0;
        foreach ($detailsWithPaket as $detail) {
            $subtotal += $this->calculateNormalPrice($detail, $pelangganId, $detailsWithPaket);
        }
        $subtotal = round($subtotal, 2);

        // Calculate pembulatan at total level based on final subtotal
        // This ensures grand total reaches the next 1000 if needed
        $pembulatan = $this->calculatePembulatan($subtotal);
        $grandTotal = $subtotal + $pembulatan;

        return [
            'subtotal' => $subtotal,
            'pembulatan' => $pembulatan,
            'grand_total' => $grandTotal
        ];
    }




    /**
     * Calculate pembulatan sesuai aturan:
     * remainder = subtotal % 1000
     * if remainder == 0: pembulatan = 0
     * else if remainder >= 1 && remainder <= 50: pembulatan = 0 (tidak dibulatkan jika selisih sangat kecil)
     * else if remainder >= 51 && remainder <= 499: pembulatan = 500 - remainder
     * else if remainder >= 500 && remainder <= 999: pembulatan = 1000 - remainder
     */
    public function calculatePembulatan(float $subtotal): float
    {
        $remainder = fmod($subtotal, 1000); // Use fmod for float precision
        $remainder = round($remainder); // Round to nearest integer
        
        if ($remainder == 0) {
            return 0;
        } elseif ($remainder >= 1 && $remainder <= 50) {
            // Tidak dibulatkan jika selisih sangat kecil (<=50)
            return 0;
        } elseif ($remainder >= 51 && $remainder <= 499) {
            // Bulat ke 500
            return (500 - $remainder);
        } else {
            // remainder >= 500, bulat ke 1000
            return (1000 - $remainder);
        }
    }







    /**
     * Get customer type following frontend logic
     */
    private function getCustomerType(?int $pelangganId): array
    {
        if (!$pelangganId) {
            return ['type' => 'normal', 'ongkos' => 0, 'is_modal' => false];
        }
        
        // Get pelanggan with ongkos field
        $pelanggan = \App\Models\Pelanggan::select('id', 'nama_pelanggan', 'jenis', 'ongkos')
            ->find($pelangganId);
        
        if (!$pelanggan) {
            return ['type' => 'normal', 'ongkos' => 0, 'is_modal' => false];
        }
        
        // Check Modal (jenis = 'modal' atau null/unknown) - following frontend
        $isModal = in_array($pelanggan->jenis, ['modal', null, 'tidak_diketahui']);
        
        // Check Antar (jenis = 'antar') - following frontend
        $isAntar = $pelanggan->jenis === 'antar';
        
        $ongkos = $pelanggan->ongkos ?? 0;
        
        if ($isModal) {
            return ['type' => 'modal', 'ongkos' => 0, 'is_modal' => true];
        } elseif ($isAntar) {
            return ['type' => 'antar', 'ongkos' => $ongkos, 'is_modal' => false];
        } else {
            // Check by name for backward compatibility
            $nama = strtolower($pelanggan->nama_pelanggan);
            if ($nama === 'kedai kopi') {
                return ['type' => 'kedai_kopi', 'ongkos' => 0, 'is_modal' => false];
            } elseif ($nama === 'hubuan') {
                return ['type' => 'hubuan', 'ongkos' => 0, 'is_modal' => false];
            }
        }
        
        return ['type' => 'normal', 'ongkos' => 0, 'is_modal' => false];
    }


    /**
     * Check if barang is in active paket - following frontend logic
     */
    private function isBarangInActivePaket(int $barangId, array $currentDetail, array $allDetails = []): bool
    {
        // Check if barang is in any active paket
        $pakets = \App\Models\Paket::whereHas('details', function($q) use ($barangId) {
            $q->where('barang_id', $barangId);
        })->where('status', 'aktif')->get();
        
        foreach ($pakets as $paket) {
            $totalQty = $this->getTotalQtyForPaket($paket, $allDetails);
            if ($totalQty >= $paket->total_qty) {
                return true;
            }
        }
        
        return false;
    }


    /**
     * Get total qty for paket based on current details
     */
    private function getTotalQtyForPaket($paket, array $allDetails = []): float
    {
        // Return 0 if no details provided
        if (empty($allDetails)) {
            return 0;
        }
        
        $paketBarangIds = $paket->details->pluck('barang_id')->toArray();
        $totalQty = 0;
        
        foreach ($allDetails as $detail) {
            if (in_array($detail['barang_id'], $paketBarangIds)) {
                $totalQty += $detail['qty'];
            }
        }
        
        return $totalQty;
    }

    /**
     * Pembulatan per item following frontend logic
     */
    private function pembulatanSubtotal(float $subtotal): float
    {
        $remainder = round($subtotal % 1000);
        $pembulatan = 0;

        if ($remainder === 0) {
            $pembulatan = 0;
        } else if ($remainder >= 1 && $remainder <= 499) {
            // Bulat ke 500
            $pembulatan = 500 - $remainder;
        } else {
            // remainder >= 500, bulat ke 1000
            $pembulatan = 1000 - $remainder;
        }

        return $subtotal + $pembulatan;
    }

    /**
     * Get pakets with priority: 'tidak' first, then 'campur'
     */
    private function getPaketsByPriority(bool $orderByPrice = false): \Illuminate\Database\Eloquent\Collection
    {
        if ($orderByPrice) {
            // For determinePaket: sort by harga ascending after prioritizing jenis
            $paketsTidak = \App\Models\Paket::with('details')
                ->where('status', 'aktif')
                ->where('jenis', 'tidak')
                ->orderBy('harga')
                ->get();
            
            $paketsCampur = \App\Models\Paket::with('details')
                ->where('status', 'aktif')
                ->where('jenis', 'campur')
                ->orderBy('harga')
                ->get();
            
            return $paketsTidak->merge($paketsCampur);
        } else {
            // For updateHargaJualForPaket: prioritize 'tidak', then 'campur' without price sorting
            $paketsTidak = \App\Models\Paket::with('details')
                ->where('status', 'aktif')
                ->where('jenis', 'tidak')
                ->get();
            
            $paketsCampur = \App\Models\Paket::with('details')
                ->where('status', 'aktif')
                ->where('jenis', 'campur')
                ->get();
            
            return $paketsTidak->merge($paketsCampur);
        }
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
     * Prioritas: jenis 'tidak' dulu, lalu harga terendah
     */
    private function updateHargaJualForPaket(array $details): array
    {
        // Get all active pakets with details, prioritizing jenis 'tidak' then 'campur'
        $pakets = $this->getPaketsByPriority(true); // true untuk sort by price

        // Find the best paket that matches ALL items in details
        $bestPaket = null;
        $paketBarangIds = [];

        foreach ($pakets as $paket) {
            $paketBarangIds = $paket->details->pluck('barang_id')->toArray();
            $detailBarangIds = array_column($details, 'barang_id');
            
            // Check if ALL items in details are SUBSET of paket items
            $allItemsMatch = true;
            foreach ($detailBarangIds as $detailBarangId) {
                if (!in_array($detailBarangId, $paketBarangIds)) {
                    $allItemsMatch = false;
                    break;
                }
            }
            
            // Check if total qty is sufficient
            $totalQty = collect($details)->whereIn('barang_id', $paketBarangIds)->sum('qty');
            
            if ($allItemsMatch && $totalQty >= $paket->total_qty) {
                // This paket applies, check if it's better than current best
                if (!$bestPaket || 
                    ($paket->jenis === 'tidak' && $bestPaket->jenis !== 'tidak') ||
                    ($paket->jenis === $bestPaket->jenis && $paket->harga < $bestPaket->harga)) {
                    $bestPaket = $paket;
                }
            }
        }


        // If a suitable paket is found, apply it to ALL items in details
        if ($bestPaket) {
            // For paket jenis 'campur', use floor() instead of round()
            // sesuai requirement: floor(harga/total_qty)
            if ($bestPaket->jenis === 'campur') {
                $hargaSatuan = floor($bestPaket->harga / $bestPaket->total_qty);
            } else {
                // For other paket types, use round() as before
                $hargaSatuan = round($bestPaket->harga / $bestPaket->total_qty);
            }
            
            // Apply paket price to ALL items in details
            foreach ($details as $index => $detail) {
                $details[$index]['harga_jual'] = $hargaSatuan;
            }
        }

        return $details;
    }


    /**
     * Determine which paket applies based on purchased items
     * Returns paket details if applicable, null otherwise
     */
    public function determinePaket(array $details): ?array
    {
        // Get active pakets with details, prioritizing jenis 'tidak' then 'campur', sorted by harga ascending
        $pakets = $this->getPaketsByPriority(true);

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
        
        // For paket jenis 'campur', use floor() instead of round()
        // sesuai requirement: floor(harga/total_qty)
        if ($selectedPaket->jenis === 'campur') {
            $hargaSatuan = floor($hargaPerPaket / $selectedPaket->total_qty);
        } else {
            // For other paket types, use round() as before
            $hargaSatuan = round($hargaPerPaket / $selectedPaket->total_qty, 2);
        }
        
        $totalHargaPaket = $jumlahPaket * $hargaPerPaket;

        return [
            'jumlah_paket' => $jumlahPaket,
            'harga_per_paket' => $hargaPerPaket,
            'harga_satuan' => $hargaSatuan,
            'total_harga_paket' => $totalHargaPaket
        ];
    }
}
