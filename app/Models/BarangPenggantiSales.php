<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangPenggantiSales extends Model
{
    protected $table = 'barang_pengganti_sales';

    protected $fillable = [
        'supplier_id',
        'barang_id',
        'qty',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'integer',
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
