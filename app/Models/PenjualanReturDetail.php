<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanReturDetail extends Model
{
    public $timestamps = false;

    protected $table = 'penjualan_retur_detail';

    protected $fillable = [
        'penjualan_retur_id',
        'barang_id',
        'satuan_id',
        'qty',
        'qty_konversi',
        'harga',
        'total',
        'created_by',
        'updated_by',
        'updated_at',
        'created_at'
    ];
}
