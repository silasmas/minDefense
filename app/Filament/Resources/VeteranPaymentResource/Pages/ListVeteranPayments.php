<?php

namespace App\Filament\Resources\VeteranPaymentResource\Pages;

use App\Filament\Resources\VeteranPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVeteranPayments extends ListRecords
{
    protected static string $resource = VeteranPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
