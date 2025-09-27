<?php

namespace App\Filament\Resources\StateAssetResource\Pages;

use App\Filament\Resources\StateAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStateAsset extends EditRecord
{
    protected static string $resource = StateAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
