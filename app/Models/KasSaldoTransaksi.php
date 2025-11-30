<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasSaldoTransaksi extends Model
{
    protected $table = 'kas_saldo_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'kas_saldo_id',
        'tipe',
        'saldo_awal',
        'saldo_akhir',
        'keterangan',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function kasSaldo()
    {
        return $this->belongsTo(KasSaldo::class, 'kas_saldo_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
