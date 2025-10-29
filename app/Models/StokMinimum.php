<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMinimum extends Model
{
    use HasFactory;

    protected $table = 'stok_minimum';

    protected $fillable = [
        'barang_id',
        'jumlah_minimum',
        'satuan_id',
        'jumlah_satuan_terkecil',
        'satuan_terkecil_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jumlah_minimum' => 'integer',
        'jumlah_satuan_terkecil' => 'integer',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    public function satuanTerkecil(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_terkecil_id');
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
