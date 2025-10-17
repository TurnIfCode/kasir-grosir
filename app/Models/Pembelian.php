<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    public $timestamps = false;

    protected $table = 'pembelian';

    protected $fillable = [
        'kode_pembelian',
        'tanggal_pembelian',
        'supplier_id',
        'subtotal',
        'diskon',
        'ppn',
        'total',
        'status',
        'catatan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ppn' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
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
