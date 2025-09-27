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
    // App\Filament\Resources\StateAssetResource\Pages\EditStateAsset.php
protected function mutateFormDataBeforeSave(array $data): array
{
    $data['asset_code'] = strtoupper(trim($data['asset_code']));
    $data['lat'] = $data['lat'] === '' ? null : (float) $data['lat'];
    $data['lng'] = $data['lng'] === '' ? null : (float) $data['lng'];
    return $data;
}

}
