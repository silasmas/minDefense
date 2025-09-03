<?php

namespace App\Filament\Resources\VeteranResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Paiements';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_month')->label('Mois')->date('m/Y')->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money(fn ($r) => $r->currency ?? 'CDF', locale: 'fr_FR')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge(),
                Tables\Columns\TextColumn::make('paid_at')->label('Payé le')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->toggleable(isToggledHiddenByDefault:true),
            ])
            ->defaultSort('period_month', 'desc');
    }
}
