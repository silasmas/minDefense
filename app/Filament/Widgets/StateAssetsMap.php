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
            ->select('id', 'title', 'lat', 'lng', 'province', 'city')
            ->limit(800) // évite surcharge
            ->get()
            ->map(fn ($a) => [
                'lat'   => (float) $a->lat,
                'lng'   => (float) $a->lng,
                'label' => trim(($a->title ?? 'Bien').' — '.($a->city ?? '').', '.($a->province ?? '')),
                'url'   => route('filament.admin.resources.state-assets.view', $a), // adapte ton panel/slug si besoin
            ])
            ->values()
            ->all();

        // Centrage par défaut sur la RDC
        $center = ['lat' => -2.88, 'lng' => 23.65, 'zoom' => 5];

        return compact('points', 'center');
    }
}
