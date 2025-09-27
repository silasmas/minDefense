<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeteranAsset extends Model
{
      /** @use HasFactory<\Database\Factories\VeteranAssetFactory> */
    use HasFactory;
    protected $fillable = [
        'veteran_id','asset_type','category','title','description',
        'estimated_value','currency','status','acquired_at','disposed_at',
        'country_code','province','city','address','lat','lng','photos',
    ];

    // Casts utiles (dates + json)
    protected $casts = [
        'acquired_at' => 'date',
        'disposed_at' => 'date',
        'photos'      => 'array',
        'lat'         => 'float',
        'lng'         => 'float',
    ];

    public function veteran(): BelongsTo
    {
        return $this->belongsTo(Veteran::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VeteranAssetLog::class, 'asset_id')->latest('occurred_at');
    }
}
