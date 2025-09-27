<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StateAssetAssignment extends Model
{
      /** @use HasFactory<\Database\Factories\StateAssetAssignmentFactory> */
    use HasFactory;
    protected $fillable = [
        'asset_id','assignee_type','veteran_id','service_name',
        'assigned_at','returned_at','status','notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(StateAsset::class, 'asset_id');
    }

    public function veteran(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Veteran::class, 'veteran_id');
    }
}
