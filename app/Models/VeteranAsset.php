<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class VeteranAsset extends Model
{
      /** @use HasFactory<\Database\Factories\VeteranAssetFactory> */
    use HasFactory;
    protected $guarded = [];

    // Casts utiles (dates + json)
    protected $casts = [
        'acquired_at' => 'date',
        'disposed_at' => 'date',
        'photos'      => 'array',
        'lat'         => 'float',
        'lng'         => 'float',
        'footprint' => 'array',
        'extent_side_m' => 'int',
    ];

    public function veteran(): BelongsTo
    {
        return $this->belongsTo(Veteran::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VeteranAssetLog::class, 'asset_id')->latest('occurred_at');
    }
        /** Filtre par texte (nom ou code) */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $term = trim($term);
        return $q->where(function($qq) use ($term) {
            $qq->where('title', 'like', "%{$term}%")
               ->orWhere('asset_code', 'like', "%{$term}%");
        });
    }

    /** Filtre type(s) */
    public function scopeTypes(Builder $q, ?array $types): Builder
    {
        if (!$types || count($types) === 0) return $q;
        return $q->whereIn('asset_type', $types);
    }

    /** Filtre status */
    public function scopeStatuses(Builder $q, ?array $statuses): Builder
    {
        if (!$statuses || count($statuses) === 0) return $q;
        return $q->whereIn('status', $statuses);
    }

    /** Filtre catégories matériel  */
    public function scopeCategories(Builder $q, ?array $cats): Builder
    {
        if (!$cats || count($cats) === 0) return $q;
        return $q->whereIn('material_category', $cats);
    }
}
