<?php

namespace App\Filament\Widgets;

use App\Models\StateAsset;
use App\Models\VeteranAsset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetsStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total      = StateAsset::count();
        $materiel   = StateAsset::where('asset_type','materiel')->count();
        $immobilier = StateAsset::where('asset_type','immobilier')->count();
        $actifs     = StateAsset::where('status','active')->count();
        $valueCdf   = (float) StateAsset::where('currency','CDF')->sum('estimated_value');
        $valueUsd   = (float) StateAsset::where('currency','USD')->sum('estimated_value');

        return [
            Stat::make('Biens (total)', number_format($total, 0, ',', ' '))
                ->description('Répartition matériel / immobilier')
                ->descriptionIcon('heroicon-m-cube'),

            Stat::make('Matériel', number_format($materiel, 0, ',', ' '))
                ->description('Biens de type matériel')
                ->color('info'),

            Stat::make('Immobilier', number_format($immobilier, 0, ',', ' '))
                ->description('Biens de type immobilier')
                ->color('success'),

            Stat::make('Actifs', number_format($actifs, 0, ',', ' '))
                ->description('Statut “Actif”')
                ->color('primary'),

            Stat::make('Valeur estimée (CDF)', number_format($valueCdf, 0, ' ', ' '))
                ->description('Somme des valeurs CDF')
                ->color('warning'),

            Stat::make('Valeur estimée (USD)', number_format($valueUsd, 0, ' ', ' '))
                ->description('Somme des valeurs USD')
                ->color('warning'),
        ];
    }
}
