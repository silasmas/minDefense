<?php

namespace App\Filament\Resources\VeteranResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';
    protected static ?string $title = 'Historique de statuts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('set_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Par'),
                Tables\Columns\TextColumn::make('comment')->label('Commentaire')->limit(60),
            ])
            ->defaultSort('set_at', 'desc');
    }
}
