<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'paket';

    protected $fillable = [
        'nama',
        'total_qty',
        'harga',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_qty' => 'integer',
        'status' => 'string',
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
