<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT. Sumber Makmur',
                'alamat' => 'Jl. Raya Industri No. 123, Jakarta',
                'telepon' => '021-12345678',
                'email' => 'contact@sumbermakmur.com',
                'kontak_person' => 'Budi Santoso',
                'status' => 'AKTIF'
            ],
            [
                'nama_supplier' => 'CV. Berkah Abadi',
                'alamat' => 'Jl. Pahlawan No. 45, Bandung',
                'telepon' => '022-87654321',
                'email' => 'info@berkahabadi.com',
                'kontak_person' => 'Siti Aminah',
                'status' => 'AKTIF'
            ],
            [
                'nama_supplier' => 'UD. Maju Jaya',
                'alamat' => 'Jl. Merdeka No. 67, Surabaya',
                'telepon' => '031-11223344',
                'email' => 'sales@majujaya.com',
                'kontak_person' => 'Ahmad Rahman',
                'status' => 'AKTIF'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
