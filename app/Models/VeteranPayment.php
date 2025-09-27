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
     // Colonnes autorisées en écriture (correspondent à ta migration)
    protected $fillable = [
        'veteran_id',   // FK -> veterans.id
        'case_id',      // FK -> veteran_cases.id (nullable)
        'payment_type', // enum: pension | arrears | aid
        'period_month', // date: premier jour du mois de référence
        'period_start', // date: début réel de la période
        'period_end',   // date: fin réelle de la période
        'amount',       // decimal(12,2): montant
        'currency',     // char(3): ex USD/CDF/EUR
        'status',       // enum: scheduled | paid | failed | refunded
        'paid_at',      // datetime: quand le paiement a été réellement payé
        'reference',    // ref bancaire / momo / pièce comptable
        'notes',        // texte libre
    ];

    // Casts pour manipuler facilement les dates et montants
    protected $casts = [
        'period_month' => 'date',
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
        'amount'       => 'decimal:2',
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
