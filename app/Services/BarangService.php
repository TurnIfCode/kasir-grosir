<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;

class BarangService
{
    /**
     * Search barang by term (kode or nama)
     * Returns array of barang data for autocomplete
     */
    public function search(string $term): array
    {
        $barangs = Barang::where('status', 'aktif')
            ->where(function ($query) use ($term) {
                $query->where('kode_barang', 'LIKE', "%{$term}%")
                      ->orWhere('nama_barang', 'LIKE', "%{$term}%");
            })
            ->select('id', 'kode_barang', 'nama_barang', 'stok')
            ->limit(10)
            ->get()
            ->toArray();

        return $barangs;
    }

    /**
     * Get available satuan options for a barang
     * Returns array of satuan data with conversion info
     */
    public function getSatuanByBarang(int $barangId): array
    {
        $barang = Barang::findOrFail($barangId);

        // Get satuan dasar
        $satuanDasar = [
            'satuan_id' => $barang->satuan_id,
            'nama_satuan' => $barang->satuan->nama_satuan ?? 'Satuan Dasar',
            'nilai_konversi' => 1,
            'harga_beli_default' => $barang->harga_beli ?? 0
        ];

        // Get konversi satuan
        $konversiSatuans = KonversiSatuan::where('barang_id', $barangId)
            ->where('status', 'aktif')
            ->with('satuanKonversi')
            ->get()
            ->map(function ($konversi) {
                return [
                    'satuan_id' => $konversi->satuan_konversi_id,
                    'nama_satuan' => $konversi->satuanKonversi->nama_satuan,
                    'nilai_konversi' => $konversi->nilai_konversi,
                    'harga_beli_default' => $konversi->harga_beli ?? 0
                ];
            })
            ->toArray();

        return array_merge([$satuanDasar], $konversiSatuans);
    }
}
