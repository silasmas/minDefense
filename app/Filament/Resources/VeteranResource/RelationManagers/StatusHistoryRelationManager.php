<?php

namespace App\Filament\Resources\VeteranResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';
    protected static ?string $title = 'Historique de statuts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('case.case_number')->label('Dossier')->searchable(),
                Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                    'warning' => 'draft',
                    'info'    => 'submitted',
                    'gray'    => 'under_review',
                    'success' => 'approved',
                    'danger'  => 'rejected',
                    'secondary' => 'closed',
                ]),
                Tables\Columns\TextColumn::make('set_at')->label('Quand')->dateTime(),
                Tables\Columns\TextColumn::make('comment')->label('Commentaire')->wrap(),
            ])
            ->defaultSort('set_at', 'desc')
            ->paginated([10,25,50])
            ->emptyStateHeading('Aucun historique');
    }
}
