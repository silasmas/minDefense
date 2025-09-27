<?php

namespace App\Filament\Resources\StateAssetResource\Pages;

use App\Filament\Resources\StateAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStateAsset extends CreateRecord
{
    protected static string $resource = StateAssetResource::class;
    protected ?string $heading = 'Enregistrer un nouveau bien de l’État';
    // App\Filament\Resources\StateAssetResource\Pages\CreateStateAsset.php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Ex: remettre en majuscules, transformer '' en null, générer un code si vide, etc.
    $data['asset_code'] = strtoupper(trim($data['asset_code']));
    $data['lat'] = $data['lat'] === '' ? null : (float) $data['lat'];
    $data['lng'] = $data['lng'] === '' ? null : (float) $data['lng'];

    if (blank($data['asset_code'])) {
        $data['asset_code'] = 'ETAT-'.now()->format('Y').'-'.str()->upper(str()->random(6));
    }

    return $data;
}

}
