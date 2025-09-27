<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StateAssetLog extends Model
{
        /** @use HasFactory<\Database\Factories\StateAssetLogFactory> */
    use HasFactory;
    protected $fillable = ['asset_id','event_type','notes','cost','currency','occurred_at','lat','lng'];

    protected $casts = [
        'occurred_at' => 'datetime',
        'cost' => 'float',
        'lat'  => 'float',
        'lng'  => 'float',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(StateAsset::class, 'asset_id');
    }
}
