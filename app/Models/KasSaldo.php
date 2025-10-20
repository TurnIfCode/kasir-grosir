<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasSaldo extends Model
{
    protected $table = 'kas_saldo';

    protected $fillable = [
        'sumber_kas',
        'saldo_awal',
        'saldo_akhir',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
