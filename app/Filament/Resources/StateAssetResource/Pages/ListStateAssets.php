<?php

namespace App\Filament\Resources\StateAssetResource\Pages;

use App\Filament\Resources\StateAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStateAssets extends ListRecords
{
    protected static string $resource = StateAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
