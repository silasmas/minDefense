<?php

namespace App\Filament\Resources\VeteranAssetResource\Pages;

use App\Filament\Resources\VeteranAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVeteranAsset extends ViewRecord
{
    protected static string $resource = VeteranAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
