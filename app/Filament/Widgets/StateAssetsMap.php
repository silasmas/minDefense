<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\StateAsset;

class StateAssetsMap extends Widget
{
    protected static string $view = 'widgets.state-assets-map';
    protected static ?string $heading = 'Carte des biens de l’État';
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $points = StateAsset::query()
            ->whereNotNull('lat')->whereNotNull('lng')
            ->select([
                'id','asset_code','title','asset_type','material_category',
                'lat','lng','extent_side_m','footprint',
            ])
            ->latest('id')->limit(1000)->get()
            ->map(function (StateAsset $a) {
                return [
                    'id'        => $a->id,
                    'code'      => $a->asset_code,
                    'title'     => $a->title,
                    'type'      => $a->asset_type,            // materiel|immobilier
                    'category'  => $a->material_category,     // vehicle|...
                    'lat'       => (float)$a->lat,
                    'lng'       => (float)$a->lng,
                    'extent'    => (int)($a->extent_side_m ?? 0),
                    'footprint' => $a->footprint ?: null,     // [[lat,lng]...]
                    'icon'      => $a->material_image_url,    // accessor ci-dessus
                    'url'       => route('filament.admin.resources.state-assets.view', $a),
                ];
            })->values()->all();

        $center = ['lat' => -2.88, 'lng' => 23.65, 'zoom' => 5]; // RDC
        // ➕ ajoute cette ligne :
    $searchUrl = route('admin.api.state-assets.index'); // l’endpoint JSON de recherche

       return compact('points', 'center', 'searchUrl'); // <-- inclure searchUrl ici
    }
}
