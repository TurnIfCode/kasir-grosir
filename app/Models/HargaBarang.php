<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaBarang extends Model
{
    public $timestamps = false;
    protected $table = 'harga_barang';

    protected $fillable = [
        'barang_id',
        'satuan_id',
        'tipe_harga',
        'harga',
        'status'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
