<?php

namespace App\Models;

use App\Models\Veteran;
use App\Models\VeteranCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseStatusHistory extends Model
{
    use HasFactory;
    protected $table = 'case_status_history';
    protected $fillable = [
        'case_id','status','set_by_user_id','set_at','comment',
    ];

    protected $casts = [
        'set_at' => 'datetime',
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
