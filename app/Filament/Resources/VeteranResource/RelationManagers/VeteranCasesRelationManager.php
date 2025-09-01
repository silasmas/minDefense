<?php

namespace App\Filament\Resources\VeteranResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class VeteranCasesRelationManager extends RelationManager
{
    protected static string $relationship = 'cases';
    protected static ?string $title = 'Dossiers';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('case_number')
                    ->label('N° dossier')->required()->maxLength(32)->unique(ignoreRecord: true),
                Forms\Components\Select::make('case_type')->label('Type')->options([
                    'status' => 'Statut',
                    'pension' => 'Pension',
                    'healthcard' => 'Carte de soins',
                    'aid' => 'Aide',
                ])->required()->native(false),
                Forms\Components\Select::make('current_status')->label('Statut')->options([
                    'draft' => 'Brouillon',
                    'submitted' => 'Soumis',
                    'under_review' => 'En instruction',
                    'approved' => 'Approuvé',
                    'rejected' => 'Rejeté',
                    'closed' => 'Clôturé',
                ])->required()->native(false),
                Forms\Components\DateTimePicker::make('opened_at')->label('Ouvert le'),
                Forms\Components\DateTimePicker::make('closed_at')->label('Clôturé le'),
                Forms\Components\Textarea::make('summary')->label('Résumé')->columnSpanFull(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('case_number')
            ->columns([
                Tables\Columns\TextColumn::make('case_number')->label('N°'),
                Tables\Columns\TextColumn::make('case_type')->label('Type')->badge(),
                Tables\Columns\BadgeColumn::make('current_status')->label('Statut')
                    ->colors([
                        'warning' => 'draft',
                        'info'    => 'submitted',
                        'gray'    => 'under_review',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'secondary' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('opened_at')->dateTime()->label('Ouvert le'),
                Tables\Columns\TextColumn::make('closed_at')->dateTime()->label('Clôturé le'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
