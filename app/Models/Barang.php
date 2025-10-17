<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    public $timestamps = false;

    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'satuan_dasar',
        'stok',
        'harga_ecer',
        'harga_grosir',
        'min_grosir_qty',
        'konversi_satuan',
        'barcode',
        'deskripsi',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'stok' => 'decimal:2',
        'harga_ecer' => 'decimal:2',
        'harga_grosir' => 'decimal:2',
        'konversi_satuan' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
