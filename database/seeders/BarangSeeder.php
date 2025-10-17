<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada kategori dan satuan terlebih dahulu
        $kategori = Kategori::first() ?? Kategori::create([
            'nama_kategori' => 'Umum',
            'status' => 'AKTIF'
        ]);

        $satuan = Satuan::first() ?? Satuan::create([
            'nama_satuan' => 'Pcs',
            'status' => 'AKTIF'
        ]);

        $barangs = [
            [
                'kode_barang' => 'BRG001',
                'nama_barang' => 'Beras Premium 5kg',
                'kategori_id' => $kategori->id,
                'satuan_dasar_id' => $satuan->id,
                'stok' => 100,
                'stok_minimal' => 10,
                'status' => 'AKTIF'
            ],
            [
                'kode_barang' => 'BRG002',
                'nama_barang' => 'Minyak Goreng 2L',
                'kategori_id' => $kategori->id,
                'satuan_dasar_id' => $satuan->id,
                'stok' => 50,
                'stok_minimal' => 5,
                'status' => 'AKTIF'
            ],
            [
                'kode_barang' => 'BRG003',
                'nama_barang' => 'Gula Pasir 1kg',
                'kategori_id' => $kategori->id,
                'satuan_dasar_id' => $satuan->id,
                'stok' => 75,
                'stok_minimal' => 8,
                'status' => 'AKTIF'
            ],
            [
                'kode_barang' => 'BRG004',
                'nama_barang' => 'Telur Ayam 1kg',
                'kategori_id' => $kategori->id,
                'satuan_dasar_id' => $satuan->id,
                'stok' => 30,
                'stok_minimal' => 5,
                'status' => 'AKTIF'
            ],
            [
                'kode_barang' => 'BRG005',
                'nama_barang' => 'Susu Kental Manis 400ml',
                'kategori_id' => $kategori->id,
                'satuan_dasar_id' => $satuan->id,
                'stok' => 40,
                'stok_minimal' => 6,
                'status' => 'AKTIF'
            ]
        ];

        foreach ($barangs as $barang) {
            Barang::create($barang);
        }
    }
}
