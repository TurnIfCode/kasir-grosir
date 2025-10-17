<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    public $timestamps = false;
    protected $table = 'pembelian_detail';

    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'satuan_id',
        'qty',
        'harga_beli',
        'subtotal',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }
}
