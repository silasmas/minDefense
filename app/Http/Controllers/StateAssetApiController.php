<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StateAsset;
use Illuminate\Http\Request;

class StateAssetApiController extends Controller
{
    public function index(Request $request)
    {
        $q     = trim((string) $request->query('q', ''));
        $limit = (int) min(max((int) $request->query('limit', 20), 1), 50);

        $query = StateAsset::query()
            ->select([
                'id','asset_code','title','asset_type',
                'material_category','lat','lng','footprint',
            ])
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('asset_code', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->limit($limit);

        $data = $query->get()->map(function (StateAsset $a) {
            return [
                'id'        => $a->id,
                'code'      => $a->asset_code,
                'title'     => $a->title,
                'type'      => $a->asset_type,            // materiel|immobilier
                'category'  => $a->material_category,
                'lat'       => $a->lat,
                'lng'       => $a->lng,
                'footprint' => $a->footprint ?: null,     // [[lat,lng]...]
            ];
        });

        return response()->json(['items' => $data], 200);
    }
}
