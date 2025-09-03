<?php
namespace App\Models;

use App\Models\CaseStatusHistory;
use App\Models\VeteranCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Veteran extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'birthdate'          => 'date',
        'service_start_date' => 'date',
        'service_end_date'   => 'date',
        'card_expires_at'    => 'date',
        'card_revoked_at'    => 'datetime',
    ];
    public function statusHistory()
    {
        return $this->hasMany(
            CaseStatusHistory::class,
            'veteran_id', // Foreign key on VeteranCase...
        )->orderByDesc('set_at');
    }

    // ----- Relations
    public function cases()
    {
        return $this->hasMany(VeteranCase::class);
    }

    public function payments()
    {
        return $this->hasMany(VeteranPayment::class)->orderByDesc('period_month');
    }

    // ----- Helpers
    public function getFullNameAttribute(): string
    {
        return trim("{$this->lastname} {$this->firstname}");
    }
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        $disk = $this->photo_disk ?: 'public';
        return Storage::disk($disk)->url($this->photo_path);
    }
    public function getPhotoForColumnAttribute(): ?string
    {
        $disk = $this->photo_disk ?? 'public';
        $path = $this->photo_path;

        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        return Storage::disk($disk)->exists($path) ? $path : null;
    }
}
