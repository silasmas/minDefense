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
    /* Scopes */
    public function scopeScheduled($q) { return $q->where('status', 'scheduled'); }
    public function scopeDue($q)       { return $q->scheduled()->whereNotNull('paid_at')->where('paid_at', '<=', now()); }
    public function scopeUpcoming($q)  { return $q->scheduled()->where('paid_at', '>', now()); }
    public function scopeOverdue($q)   { return $q->scheduled()->whereNotNull('paid_at')->where('paid_at', '<=', now()); }

    /* Génère une référence unique si absente */
    public function ensureReference(): void
    {
        if ($this->reference) return;

        do {
            $ref = sprintf('REF-%d-%s-%s',
                $this->veteran_id,
                now()->format('ym'),
                \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5))
            );
        } while (self::where('reference', $ref)->exists());

        $this->reference = $ref;
    }

    /** Exécuter le paiement (le marquer 'paid') */
    public function execute(?\Carbon\Carbon $at = null, ?string $ref = null): bool
    {
        $this->status  = 'paid';
        $this->paid_at = $at ?: now();
        if ($ref) $this->reference = $ref;
        $this->ensureReference();

        return $this->save();
    }

    /** Annuler (on passe à 'refunded', on vide paid_at) */
    public function cancel(?string $reason = null): bool
    {
        $this->status  = 'refunded';
        $this->paid_at = null;

        if ($reason) {
            $this->notes = trim(($this->notes ? $this->notes . PHP_EOL : '') .
                'Annulé le ' . now()->format('d/m/Y H:i') . ' — ' . $reason);
        }
        return $this->save();
    }
}
