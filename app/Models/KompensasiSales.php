<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KompensasiSales extends Model
{
    protected $table = 'kompensasi_sales';

    protected $fillable = [
        'supplier_id',
        'jumlah_kompensasi',
        'barang_id',
        'qty_rusak',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'jumlah_kompensasi' => 'decimal:2',
        'qty_rusak' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
