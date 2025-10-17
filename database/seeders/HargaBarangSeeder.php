<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HargaBarang;
use App\Models\Barang;
use App\Models\Satuan;

class HargaBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangs = Barang::where('status', 'AKTIF')->get();
        $satuan = Satuan::where('nama_satuan', 'Pcs')->first();

        if ($barangs->isEmpty() || !$satuan) {
            return;
        }

        $hargaData = [
            ['barang_kode' => 'BRG001', 'harga' => 65000, 'konversi' => 1],
            ['barang_kode' => 'BRG002', 'harga' => 25000, 'konversi' => 1],
            ['barang_kode' => 'BRG003', 'harga' => 15000, 'konversi' => 1],
            ['barang_kode' => 'BRG004', 'harga' => 28000, 'konversi' => 1],
            ['barang_kode' => 'BRG005', 'harga' => 12000, 'konversi' => 1],
        ];

        foreach ($hargaData as $data) {
            $barang = $barangs->where('kode_barang', $data['barang_kode'])->first();
            if ($barang) {
                HargaBarang::create([
                    'barang_id' => $barang->id,
                    'satuan_id' => $satuan->id,
                    'harga' => $data['harga'],
                    'konversi_ke_satuan_dasar' => $data['konversi'],
                    'status' => 'AKTIF'
                ]);
            }
        }
    }
}
