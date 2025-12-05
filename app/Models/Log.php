<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;
    protected $table = 'log';

    protected $fillable = [
        'keterangan',
        'created_by',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
