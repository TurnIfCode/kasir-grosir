<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanRetur extends Model
{
    public $timestamps = false;

    protected $table = 'penjualan_retur';

    protected $fillable = [
        'kode_retur',
        'kode_penjualan',
        'grand_total_penjualan',
        'grand_total_retur',
        'created_by',
        'updated_by',
        'updated_at',
        'created_at'
    ];
}
