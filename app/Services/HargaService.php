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
    public function lookupHarga(int $barangId, int $satuanId, string $tipe = 'ecer', ?int $pelangganId = null): array
    {
        // Check if pelanggan is "Kedai Kopi"
        $isKedaiKopi = false;
        if ($pelangganId) {
            $pelanggan = \App\Models\Pelanggan::find($pelangganId);
            $isKedaiKopi = $pelanggan && strtolower($pelanggan->nama_pelanggan) === 'kedai kopi';
        }

        if ($isKedaiKopi) {
            // For Kedai Kopi, use harga_beli instead of harga_jual
            $barang = Barang::findOrFail($barangId);

            // Check if satuan matches barang's satuan utama
            if ($barang->satuan_id == $satuanId && $barang->harga_beli) {
                return [
                    'harga' => $barang->harga_beli,
                    'nilai_konversi' => 1,
                    'source' => 'barang_harga_beli'
                ];
            }

            // Check konversi_satuan for harga_beli
            $konversi = \App\Models\KonversiSatuan::where('barang_id', $barangId)
                ->where('satuan_konversi_id', $satuanId)
                ->where('status', 'aktif')
                ->first();

            if ($konversi && $konversi->harga_beli) {
                return [
                    'harga' => $konversi->harga_beli,
                    'nilai_konversi' => $konversi->nilai_konversi,
                    'source' => 'konversi_satuan_harga_beli'
                ];
            }

            // Fallback to barang harga_beli if no konversi
            if ($barang->harga_beli) {
                return [
                    'harga' => $barang->harga_beli,
                    'nilai_konversi' => 1,
                    'source' => 'barang_harga_beli_fallback'
                ];
            }

            throw new \Exception("Harga beli tidak ditemukan untuk barang ID {$barangId}, satuan ID {$satuanId}");
        }

        // Normal pricing logic for other customers
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
