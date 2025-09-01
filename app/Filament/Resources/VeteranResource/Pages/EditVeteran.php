<?php

namespace App\Filament\Resources\VeteranResource\Pages;

use App\Filament\Resources\VeteranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVeteran extends EditRecord
{
    protected static string $resource = VeteranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
