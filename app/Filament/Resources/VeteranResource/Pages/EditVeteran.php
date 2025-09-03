<?php

namespace App\Filament\Resources\VeteranResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VeteranResource;
use Filament\Resources\Pages\Concerns\HasRelationManagers;

class EditVeteran extends EditRecord
{
    use HasRelationManagers; // ← indispensable pour voir les onglets relations
    protected static string $resource = VeteranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
