<?php

namespace App\Http\Controllers;

use App\Models\VeteranAsset;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVeteranAssetRequest;
use App\Http\Requests\UpdateVeteranAssetRequest;

class VeteranAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Sécurité: assure-toi que cette route est protégée par auth + can
        $q        = $request->string('q')->toString();
        $types    = $request->input('types', []);    // ['immobilier','materiel']
        $statuses = $request->input('statuses', []); // ['actif','inactif']
        $cats     = $request->input('categories', []);// ['vehicle','computer',...]

        $limit = (int) min(max((int)$request->input('limit', 2000), 1), 5000);

        $assets = VeteranAsset::query()
            ->whereNotNull('lat')->whereNotNull('lng')
            ->search($q)
            ->types(is_array($types) ? $types : [])
            ->statuses(is_array($statuses) ? $statuses : [])
            ->categories(is_array($cats) ? $cats : [])
            ->latest('id')
            ->limit($limit)
            ->get();

        // Retour en GeoJSON FeatureCollection
        $features = $assets->map(function(VeteranAsset $a) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [ (float)$a->lng, (float)$a->lat ],
                ],
                'properties' => [
                    'id'       => $a->id,
                    'code'     => $a->asset_code,
                    'title'    => $a->title,
                    'type'     => $a->asset_type,           // immobilier|materiel
                    'category' => $a->material_category,
                    'status'   => $a->status,
                    'city'     => $a->city,
                    'province' => $a->province,
                    'icon'     => $a->image_url,            // optionnel (matériel)
                    'extent'   => (int)($a->extent_side_m ?? 0),
                    'footprint'=> $a->footprint ?: null,    // [[lat,lng]...]
                    'url'      => route('filament.admin.resources.veteran-assets.view', $a), // adapte la route
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVeteranAssetRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VeteranAsset $veteranAsset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VeteranAsset $veteranAsset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVeteranAssetRequest $request, VeteranAsset $veteranAsset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VeteranAsset $veteranAsset)
    {
        //
    }
}
