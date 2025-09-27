<?php

namespace App\Filament\Resources\VeteranAssetResource\Pages;

use App\Filament\Resources\VeteranAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVeteranAssets extends ListRecords
{
    protected static string $resource = VeteranAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
