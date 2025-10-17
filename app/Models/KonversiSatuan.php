<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonversiSatuan extends Model
{
    public $timestamps = false;
    protected $table = 'konversi_satuan';

    protected $fillable = [
        'barang_id',
        'satuan_dasar_id',
        'satuan_konversi_id',
        'nilai_konversi',
        'harga_beli',
        'harga_jual',
        'status'
    ];

    protected $casts = [
        'nilai_konversi' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function satuanDasar()
    {
        return $this->belongsTo(Satuan::class, 'satuan_dasar_id');
    }

    public function satuanKonversi()
    {
        return $this->belongsTo(Satuan::class, 'satuan_konversi_id');
    }
}
