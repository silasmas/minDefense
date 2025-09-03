<?php

namespace App\Filament\Exports;

use App\Models\Veteran;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class VeteranExporter extends Exporter
{
    protected static ?string $model = Veteran::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('service_number'),
            ExportColumn::make('nin'),
            ExportColumn::make('firstname'),
            ExportColumn::make('lastname'),
            ExportColumn::make('birthdate'),
            ExportColumn::make('gender'),
            ExportColumn::make('phone'),
            ExportColumn::make('phone_verified_at'),
            ExportColumn::make('email'),
            ExportColumn::make('photo_path'),
            ExportColumn::make('photo_disk'),
            ExportColumn::make('address'),
            ExportColumn::make('branch'),
            ExportColumn::make('rank'),
            ExportColumn::make('card_number'),
            ExportColumn::make('card_expires_at'),
            ExportColumn::make('card_status'),
            ExportColumn::make('card_revoked_at'),
            ExportColumn::make('card_status_reason'),
            ExportColumn::make('service_start_date'),
            ExportColumn::make('service_end_date'),
            ExportColumn::make('status'),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your veteran export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
