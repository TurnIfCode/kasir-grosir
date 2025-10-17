<?php

namespace App\Services;

use App\Models\HargaBarang;
use App\Models\Barang;

class HargaService
{
    /**
     * Lookup harga for specific barang, satuan, and tipe
     * Returns harga data with conversion info
     */
    public function lookupHarga(int $barangId, int $satuanId, string $tipe = 'ecer'): array
    {
        // Try to find specific harga_barang record
        $hargaBarang = HargaBarang::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->where('tipe_harga', $tipe)
            ->where('status', 'aktif')
            ->first();

        if ($hargaBarang) {
            return [
                'harga' => $hargaBarang->harga,
                'nilai_konversi' => 1, // harga_barang is already per satuan
                'source' => 'harga_barang'
            ];
        }

        // Fallback to barang harga_jual for ecer
        $barang = Barang::findOrFail($barangId);
        if ($tipe === 'ecer' && $barang->harga_jual) {
            return [
                'harga' => $barang->harga_jual,
                'nilai_konversi' => 1,
                'source' => 'barang_ecer'
            ];
        }

        // Fallback to konversi_satuan harga_jual
        $konversi = \App\Models\KonversiSatuan::where('barang_id', $barangId)
            ->where('satuan_konversi_id', $satuanId)
            ->where('status', 'aktif')
            ->first();

        if ($konversi && $konversi->harga_jual) {
            return [
                'harga' => $konversi->harga_jual,
                'nilai_konversi' => $konversi->nilai_konversi,
                'source' => 'konversi_satuan'
            ];
        }

        throw new \Exception("Harga tidak ditemukan untuk barang ID {$barangId}, satuan ID {$satuanId}, tipe {$tipe}");
    }
}
