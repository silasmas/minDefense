<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeteranVerification extends Model
{
    /** @use HasFactory<\Database\Factories\VeteranVerificationFactory> */
    use HasFactory;

     protected $fillable = [
        'veteran_id','phone','token','purpose','next_status','payload',
        'expires_at','consumed_at','sent_at','channel',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'consumed_at' => 'datetime',
        'sent_at'     => 'datetime',
        'payload'     => 'array',
    ];

    public function scopeActive($q) {
        return $q->whereNull('consumed_at')->where('expires_at','>', now());
    }

    public function veteran() {
        return $this->belongsTo(Veteran::class);
    }
}
