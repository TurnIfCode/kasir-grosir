<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiStok extends Model
{
    protected $table = 'mutasi_stok';

    protected $fillable = [
        'barang_id',
        'qty',
        'dari_gudang',
        'ke_gudang',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
