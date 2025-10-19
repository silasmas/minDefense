<?php

// app/Http/Controllers/MapController.php
namespace App\Http\Controllers;

use App\Models\VeteranAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MapController extends Controller
{
    // Page Blade
    public function page(Request $req)
    {
        // On peut recevoir ?matricule=XYZ depuis “Vue sur la carte”
        return view('assets.map', [
            'prefillMatricule' => $req->query('matricule')
        ]);
    }

    // API GeoJSON
    public function api(Request $req)
    {
        $q = VeteranAsset::query();

        if ($req->filled('matricule')) {
            $q->where('service_number', 'like', '%'.$req->query('matricule').'%');
        }

        if ($req->filled('type')) {
            $q->whereIn('type', Arr::wrap($req->query('type')));
        }

        // Ici on pourrait ajouter pagination; pour la carte on renvoie tout (prudence si très gros)
        $assets = $q->limit(5000)->get();

        // Conversion en GeoJSON FeatureCollection
        $features = $assets->map(function ($a) {
            if ($a->type === 'immobilier' && $a->geometry) {
                // Polygone/Multipolygon déjà stocké en GeoJSON
                $geom = $a->geometry; // array|json
                if (is_string($geom)) $geom = json_decode($geom, true);
                return [
                    'type' => 'Feature',
                    'geometry' => $geom,
                    'properties' => [
                        'id' => $a->id,
                        'name' => $a->name,
                        'type' => $a->type,
                        'service_number' => $a->service_number,
                    ],
                ];
            }

            // Matériel (point) ou fallback si pas de geojson
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$a->longitude, (float)$a->latitude],
                ],
                'properties' => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'type' => $a->type,
                    'service_number' => $a->service_number,
                ],
            ];
        })->values();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
