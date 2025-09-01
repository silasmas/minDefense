<?php

namespace App\Models;

use App\Models\Veteran;
use App\Models\CaseDocument;
use App\Models\CaseStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VeteranCase extends Model
{
    use HasFactory,SoftDeletes;
// ⚠️ ta table est au singulier
   // ⚠️ ta table est au singulier
    protected $table = 'veteran_cases';
    protected $fillable = [
        'veteran_id','case_number','case_type','current_status',
        'opened_at','closed_at','summary','meta',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'meta'      => 'array',
    ];

    public function veteran()
    {
        return $this->belongsTo(Veteran::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(CaseStatusHistory::class, 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(CaseDocument::class, 'case_id');
    }
}
