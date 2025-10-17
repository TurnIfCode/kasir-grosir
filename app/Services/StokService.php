<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KonversiSatuan;
use Illuminate\Support\Facades\DB;

class StokService
{
    /**
     * Convert quantity to base unit
     * Returns quantity in base unit
     */
    public function convertToDasar(int $barangId, int $satuanId, float $qty): float
    {
        $barang = Barang::findOrFail($barangId);

        // If satuan is base unit, return qty as is
        if ($satuanId == $barang->satuan_id) {
            return $qty;
        }

        // Find conversion
        $konversi = KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->where('status', 'aktif')
            ->first();

        if (!$konversi) {
            throw new \Exception("Konversi satuan tidak ditemukan untuk barang ID {$barangId}, satuan ID {$satuanId}");
        }

        // Convert to base unit: qty * nilai_konversi
        return $qty * $konversi->nilai_konversi;
    }

    /**
     * Decrease stock for a barang
     * Uses locking to prevent race conditions
     */
    public function decreaseStock(int $barangId, float $qtyDasar, array $meta = []): void
    {
        DB::transaction(function () use ($barangId, $qtyDasar, $meta) {
            $barang = Barang::lockForUpdate()->findOrFail($barangId);

            if ($barang->stok < $qtyDasar) {
                // Optional: allow negative stock or throw error
                // For now, we'll allow negative stock but log it
                \Log::warning("Stock going negative for barang {$barangId}: current {$barang->stok}, decrease {$qtyDasar}", $meta);
            }

            $barang->decrement('stok', $qtyDasar);

            // Log stock movement if needed
            \Log::info("Stock decreased for barang {$barangId}: -{$qtyDasar}", array_merge($meta, [
                'previous_stock' => $barang->stok + $qtyDasar,
                'new_stock' => $barang->stok
            ]));
        });
    }

    /**
     * Increase stock for a barang (for rollback)
     */
    public function increaseStock(int $barangId, float $qtyDasar, array $meta = []): void
    {
        DB::transaction(function () use ($barangId, $qtyDasar, $meta) {
            $barang = Barang::lockForUpdate()->findOrFail($barangId);
            $barang->increment('stok', $qtyDasar);

            \Log::info("Stock increased for barang {$barangId}: +{$qtyDasar}", array_merge($meta, [
                'previous_stock' => $barang->stok - $qtyDasar,
                'new_stock' => $barang->stok
            ]));
        });
    }
}
