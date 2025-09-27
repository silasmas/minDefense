<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StateAssetResource\Pages;
use App\Filament\Resources\StateAssetResource\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\StateAssetResource\RelationManagers\LogsRelationManager;
use App\Models\StateAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StateAssetResource extends Resource
{
    protected static ?string $model = StateAsset::class;

    protected static ?string $navigationIcon  = 'heroicon-m-cube';
    protected static ?string $navigationGroup = 'Patrimoine de l’État';
    protected static ?string $modelLabel      = 'Bien de l’État';
    protected static ?string $pluralModelLabel= 'Biens de l’État';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identification')->columns(3)->schema([
                Forms\Components\Select::make('asset_type')->label('Type de bien')
                    ->options(['materiel'=>'Matériel','immobilier'=>'Immobilier'])
                    ->required()->native(false),

                Forms\Components\TextInput::make('asset_code')
                    ->label('Code inventaire')->required()->maxLength(50)
                    ->helperText('Ex: ETAT-2025-000123 (unique).'),

                Forms\Components\TextInput::make('category')->label('Catégorie')->maxLength(64),

                Forms\Components\TextInput::make('title')->label('Désignation')->required()->maxLength(150),

                Forms\Components\TextInput::make('serial_number')->label('N° série (si matériel)')->maxLength(120),

                Forms\Components\Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Valeur & statut')->columns(3)->schema([
                Forms\Components\TextInput::make('estimated_value')->label('Valeur estimée')->numeric()->minValue(0),
                Forms\Components\Select::make('currency')->label('Devise')->options(['CDF'=>'CDF','USD'=>'USD'])->default('CDF')->native(false),

                Forms\Components\Select::make('status')->label('Statut')
                    ->options([
                        'active'=>'Actif', 'under_maintenance'=>'Maintenance', 'disposed'=>'Cédé',
                    ])->default('active')->native(false),

                Forms\Components\DatePicker::make('acquired_at')->label('Date d’acquisition'),
                Forms\Components\DatePicker::make('disposed_at')->label('Date de cession'),

                Forms\Components\TextInput::make('managing_agency')->label('Structure gestionnaire')->maxLength(150),
            ]),

            Forms\Components\Section::make('Localisation')->columns(3)->schema([
                Forms\Components\TextInput::make('address')->label('Adresse')->columnSpanFull(),
                Forms\Components\TextInput::make('province')->label('Province'),
                Forms\Components\TextInput::make('city')->label('Ville'),
                Forms\Components\TextInput::make('country_code')->label('Pays')->default('CD')->maxLength(2),

                Forms\Components\TextInput::make('lat')->label('Latitude')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('lng')->label('Longitude')->numeric()->step('0.0000001'),

                Forms\Components\View::make('forms.asset-map-preview')
                    ->label('Carte (aperçu)')->columnSpanFull()
                    ->visible(fn(Forms\Get $get) => filled($get('lat')) && filled($get('lng'))),
            ]),

            Forms\Components\Section::make('Médias')->schema([
                Forms\Components\FileUpload::make('photos')
                    ->label('Photos')->disk('public')->directory('assets')
                    ->multiple()->image()->imageEditor()->reorderable()->downloadable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photos.0')->label('')->circular()
                    ->size(40)->defaultImageUrl(asset('images/default.jpg')),

                Tables\Columns\TextColumn::make('asset_code')->label('Code')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Désignation')->sortable()->searchable(),

                Tables\Columns\BadgeColumn::make('asset_type')->label('Type')->colors([
                    'info' => 'materiel', 'success' => 'immobilier'
                ])->formatStateUsing(fn($state) => $state==='materiel'?'Matériel':'Immobilier'),

                Tables\Columns\TextColumn::make('category')->label('Catégorie')->toggleable(),
                Tables\Columns\TextColumn::make('province')->label('Province')->badge()->toggleable(),

                Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                    'success'=>'active','warning'=>'under_maintenance','gray'=>'disposed'
                ])->formatStateUsing(fn($state)=>[
                    'active'=>'Actif','under_maintenance'=>'Maintenance','disposed'=>'Cédé'
                ][$state]??$state),

                Tables\Columns\TextColumn::make('estimated_value')->label('Valeur')
                    ->formatStateUsing(fn($state,$record)=>$state?number_format((float)$state,0,' ',' ').' '.($record->currency??'CDF'):'—')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_type')->label('Type')
                    ->options(['materiel'=>'Matériel','immobilier'=>'Immobilier']),
                Tables\Filters\SelectFilter::make('status')->label('Statut')
                    ->options(['active'=>'Actif','under_maintenance'=>'Maintenance','disposed'=>'Cédé']),
                Tables\Filters\SelectFilter::make('province')->label('Province')
                    ->options(fn() => \App\Models\StateAsset::query()
                        ->select('province')->whereNotNull('province')->distinct()->orderBy('province')
                        ->pluck('province','province')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,        // Journal de gestion
            AssignmentsRelationManager::class, // Affectations (facultatif)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStateAssets::route('/'),
            'create' => Pages\CreateStateAsset::route('/create'),
            'view'   => Pages\ViewStateAsset::route('/{record}'),
            'edit'   => Pages\EditStateAsset::route('/{record}/edit'),
        ];
    }
}
