<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'owner_type','owner_id','phone','purpose','code','expires_at','consumed_at','attempts','ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at'=> 'datetime',
    ];

    public function scopeActive($q)
    {
        return $q->whereNull('consumed_at')->where('expires_at', '>', now());
    }
}
