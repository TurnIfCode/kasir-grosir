<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianPembayaran extends Model
{
    protected $table = 'pembelian_pembayaran';

    protected $fillable = [
        'pembelian_id',
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

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
