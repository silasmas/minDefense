<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VeteranAssetLog extends Model
{
    /** @use HasFactory<\Database\Factories\VeteranAssetLogFactory> */
    use HasFactory;
    protected $fillable = [
        'asset_id','event_type','notes','cost','currency','occurred_at','lat','lng',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
        'cost' => 'float',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(VeteranAsset::class, 'asset_id');
    }
}
