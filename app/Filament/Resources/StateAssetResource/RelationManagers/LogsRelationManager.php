<?php

namespace App\Filament\Resources\StateAssetResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'Journal de gestion';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('event_type')->label('Type')
                ->options([
                    'maintenance'=>'Maintenance','inspection'=>'Inspection','transfer'=>'Transfert',
                    'status_change'=>'Changement statut','note'=>'Note',
                ])->required()->native(false),

            Forms\Components\Textarea::make('notes')->label('Notes')->rows(3),
            Forms\Components\TextInput::make('cost')->label('Coût')->numeric()->minValue(0),
            Forms\Components\Select::make('currency')->label('Devise')->options(['CDF'=>'CDF','USD'=>'USD'])->native(false),
            Forms\Components\DateTimePicker::make('occurred_at')->label('Quand')->default(now()),
            Forms\Components\TextInput::make('lat')->label('Lat')->numeric()->step('0.0000001'),
            Forms\Components\TextInput::make('lng')->label('Lng')->numeric()->step('0.0000001'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('event_type')->label('Type'),
                Tables\Columns\TextColumn::make('occurred_at')->label('Quand')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(60),
                Tables\Columns\TextColumn::make('cost')->label('Coût')
                    ->formatStateUsing(fn($v,$r)=>$v?number_format((float)$v,0,' ',' ').' '.($r->currency??''):'—')
                    ->alignRight(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label('Ajouter')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
