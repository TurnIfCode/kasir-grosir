<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfer';

    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $fillable = [
        'bank_asal',
        'bank_tujuan',
        'nominal_transfer',
        'admin_bank',
        'grand_total',
        'catatan',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
