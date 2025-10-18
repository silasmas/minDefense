<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AssetsMapWidget extends Widget
{
    protected static string $view = 'filament.widgets.assets-map';
    protected static ?string $heading = 'Carte des biens';
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
           $panelPath = filament()->getCurrentPanel()?->getPath() ?? 'admin';
        // centre RDC + l'endpoint de recherche
        return [
            'center'   => ['lat' => -2.88, 'lng' => 23.65, 'zoom' => 5],
               // URL **sur la même origine** que le panel
            'searchUrl' => url(sprintf('/%s/api/assets', $panelPath)),
            'mapId'     => 'state-assets-leaflet',
        ];
    }
}
