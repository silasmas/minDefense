<?php

namespace App\Models;

use App\Models\VeteranCase;
use App\Models\CaseStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Veteran extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'birthdate' => 'date',
        'service_start_date' => 'date',
        'service_end_date'   => 'date',
        'card_expires_at'=>'date',
  'card_revoked_at' => 'datetime',
    ];
public function statusHistories()
{
    return $this->hasManyThrough(
        CaseStatusHistory::class,
        VeteranCase::class,
        'veteran_id', // Foreign key on VeteranCase...
        'case_id',    // Foreign key on CaseStatusHistory...
        'id',         // Local key on Veteran...
        'id'          // Local key on VeteranCase...
    );
}

    // ----- Relations
    public function cases()
    {
        return $this->hasMany(VeteranCase::class);
    }

    public function payments()
    {
        return $this->hasMany(VeteranPayment::class);
    }

    // ----- Helpers
    public function getFullNameAttribute(): string
    {
        return trim("{$this->lastname} {$this->firstname}");
    }
    public function getPhotoUrlAttribute(): ?string
{
    if (! $this->photo_path) return null;
    $disk = $this->photo_disk ?: 'public';
    return Storage::disk($disk)->url($this->photo_path);
}
}
