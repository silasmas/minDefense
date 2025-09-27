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
        return $this->hasMany(VeteranPayment::class)->orderByDesc('paid_at')
        ->orderByDesc('id');
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
     // ... tes autres relations et use ...



    /**
     * Total payé (somme des paiements appliqués)
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->where('status', 'applied')->sum('amount');
    }

    /**
     * Arriéré = total_due - total_paid
     * ATTENTION: il faut que tu aies une colonne 'total_due' ou 'debt' sur veteran
     * si non, adapte en fonction de ton modèle (ex: calcul à partir d'autres champs)
     */
    public function getArrearsAttribute(): float
    {
        $due = (float) ($this->total_due ?? 0); // adapte le champ si différent
        return max(0, $due - $this->total_paid);
    }
    public function assets()
{
    return $this->hasMany(\App\Models\VeteranAsset::class, 'veteran_id');
}
}
