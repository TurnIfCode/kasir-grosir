<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenjualanDetail extends Model
{
    public $timestamps = false;
    protected $table = 'penjualan_detail';

    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'satuan_id',
        'qty',
        'qty_konversi',
        'harga_jual',
        'harga_beli',
        'subtotal',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'qty_konversi' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
