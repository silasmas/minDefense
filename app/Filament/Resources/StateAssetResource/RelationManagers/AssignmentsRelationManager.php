<?php

namespace App\Filament\Resources\StateAssetResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';
    protected static ?string $title = 'Affectations';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('assignee_type')->label('Affecté à')
                ->options(['veteran'=>'Ancien combattant','service'=>'Service'])
                ->required()->live()->native(false),

            // si veteran
            Forms\Components\Select::make('veteran_id')->label('Ancien combattant')
                ->relationship('veteran', 'lastname')
                ->searchable()->preload()
                ->visible(fn(Forms\Get $get) => $get('assignee_type') === 'veteran'),

            // si service
            Forms\Components\TextInput::make('service_name')->label('Nom du service')
                ->visible(fn(Forms\Get $get) => $get('assignee_type') === 'service'),

            Forms\Components\DateTimePicker::make('assigned_at')->label('Affecté le')->default(now())->required(),
            Forms\Components\DateTimePicker::make('returned_at')->label('Restitué le'),
            Forms\Components\Select::make('status')->label('Statut')->options([
                'ongoing'=>'En cours', 'returned'=>'Restitué', 'lost'=>'Perdu',
            ])->default('ongoing')->native(false),
            Forms\Components\Textarea::make('notes')->label('Notes')->rows(3),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignee_type')->label('Type')->badge()
                    ->formatStateUsing(fn($s)=>$s==='veteran'?'Ancien combattant':'Service'),
                Tables\Columns\TextColumn::make('veteran.full_name')->label('Vétéran')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('service_name')->label('Service'),
                Tables\Columns\TextColumn::make('assigned_at')->label('Affecté le')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('returned_at')->label('Restitué le')->dateTime('d/m/Y H:i'),
                Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                    'primary'=>'ongoing','success'=>'returned','danger'=>'lost'
                ])->formatStateUsing(fn($s)=>['ongoing'=>'En cours','returned'=>'Restitué','lost'=>'Perdu'][$s]),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label('Nouvelle affectation')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
