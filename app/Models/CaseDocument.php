<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'case_id','doc_type','storage_disk','path','original_name','mime','size','uploaded_at','notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(VeteranCase::class, 'case_id');
    }
}
