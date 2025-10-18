<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Doit être NON statique
    protected ?string $maxContentWidth = 'full';

    // Grille de la page (widgets “contenu”)
    public function getColumns(): int|array
    {
        return 12;
    }

    // (optionnel) Grille des “header widgets” en haut de la page
    public function getHeaderWidgetsColumns(): int|array
    {
        return 12;
    }
    protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Widgets\AssetsMapWidget::class,
    ];
}
}
