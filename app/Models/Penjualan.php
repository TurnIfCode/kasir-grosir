<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    public $timestamps = false;

    protected $table = 'penjualan';

    protected $fillable = [
        'kode_penjualan',
        'tanggal_penjualan',
        'pelanggan_id',
        'total',
        'diskon',
        'ppn',
        'pembulatan',
        'grand_total',
        'jenis_pembayaran',
        'dibayar',
        'kembalian',
        'catatan',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'total' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ppn' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'dibayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(PenjualanPembayaran::class, 'penjualan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
