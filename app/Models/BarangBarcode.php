<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangBarcode extends Model
{
    protected $table = 'barang_barcodes';

    protected $fillable = [
        'barang_id',
        'barcode',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
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
