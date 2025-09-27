<?php

namespace App\Filament\Widgets;

use App\Models\VeteranAsset;
use Filament\Widgets\Widget;

class AssetsMapWidget extends Widget
{
    // protected static string $view = 'filament.widgets.assets-map';
    // protected static ?string $heading = 'Carte des biens';
    // protected int|string|array $columnSpan = 'full';

    // protected function getViewData(): array
    // {
    //     // On ne récupère que les biens géolocalisés
    //     $assets = VeteranAsset::query()
    //         ->whereNotNull('lat')->whereNotNull('lng')
    //         ->select('id','title','asset_type','status','lat','lng','province','city')
    //         ->latest('id')->limit(1000)->get();

    //     return compact('assets');
    // }
}
