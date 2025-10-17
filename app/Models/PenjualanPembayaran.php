<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenjualanPembayaran extends Model
{
    public $timestamps = false;
    protected $table = 'penjualan_pembayaran';

    protected $fillable = [
        'penjualan_id',
        'metode',
        'nominal',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }
}
