<?php

namespace App\Models;

use App\Models\Veteran;
use App\Models\VeteranCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VeteranPayment extends Model
{
    use HasFactory,SoftDeletes;
     protected $table = 'veteran_payments';
    protected $guarded = [];

    protected $casts = [
        'period_month' => 'date',   // stocké au 1er du mois
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
    ];

    public function veteran()
    {
        return $this->belongsTo(Veteran::class);
    }

    public function case()
    {
        return $this->belongsTo(VeteranCase::class, 'case_id');
    }
}
