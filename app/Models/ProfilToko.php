<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilToko extends Model
{
    protected $table = 'profil_toko';

    protected $fillable = [
        'nama_toko',
        'slogan',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'no_telp',
        'email',
        'website',
        'npwp',
        'logo',
        'deskripsi'
    ];
}
