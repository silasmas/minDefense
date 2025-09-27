<?php

namespace App\Filament\Resources\VeteranAssetResource\Pages;

use App\Filament\Resources\VeteranAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVeteranAsset extends EditRecord
{
    protected static string $resource = VeteranAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
