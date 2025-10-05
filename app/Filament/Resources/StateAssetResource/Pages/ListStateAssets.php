<?php
namespace App\Filament\Resources\StateAssetResource\Pages;

use App\Filament\Resources\StateAssetResource;
use App\Filament\Widgets\StateAssetsMap;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListStateAssets extends ListRecords
{
    protected static string $resource = StateAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
                ->label('Enregistrer un nouveau bien de l’État')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
        ];
    }
 // 👉 Ici on met des WIDGETS (pas dans getHeaderActions !)
    protected function getHeaderWidgets(): array
    {
        return [
            StateAssetsMap::class, // ton widget carte
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
         return [
        'sm' => 1,
        'lg' => 2,
    ];
    }
    // (optionnel) plein largeur pour la zone widgets
    // protected function getHeaderWidgetsColumns(): int | array
    // {
    //     return 12;
    // }
}
