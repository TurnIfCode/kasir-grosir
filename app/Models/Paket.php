<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'paket';

    protected $fillable = [
        'kode_paket',
        'nama_paket',
        'harga_per_3',
        'harga_per_unit',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'harga_per_3' => 'integer',
        'harga_per_unit' => 'integer',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PaketDetail::class);
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
