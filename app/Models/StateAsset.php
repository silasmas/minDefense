<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StateAsset extends Model
{
     /** @use HasFactory<\Database\Factories\StateAssetFactory> */
    use HasFactory;
    protected $fillable = [
        'asset_type','asset_code','category','title','description','serial_number',
        'estimated_value','currency','status','acquired_at','disposed_at',
        'country_code','province','city','address','lat','lng','managing_agency','photos',
    ];

    protected $casts = [
        'acquired_at' => 'date',
        'disposed_at' => 'date',
        'lat'         => 'float',
        'lng'         => 'float',
        'photos'      => 'array',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(StateAssetLog::class, 'asset_id')->latest('occurred_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StateAssetAssignment::class, 'asset_id')->latest('assigned_at');
    }
}
