<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VeteranAssetResource\Pages;
use App\Filament\Resources\VeteranAssetResource\RelationManagers\LogsRelationManager;
use App\Models\VeteranAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VeteranAssetResource extends Resource
{
    protected static ?string $model = VeteranAsset::class;

    protected static ?string $navigationIcon  = 'heroicon-m-cube';
    protected static ?string $navigationGroup = 'Patrimoine & paiements'; // renomme à ta convenance
    protected static ?string $modelLabel      = 'Bien';
    protected static ?string $pluralModelLabel = 'Biens';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identification')->columns(3)->schema([
                Forms\Components\Select::make('veteran_id')
                    ->relationship('veteran', 'lastname') // affiche le nom (tu peux créer accessor full_name)
                    ->searchable()
                    ->preload()
                    ->label('Ancien combattant')
                    ->required(),

                Forms\Components\Select::make('asset_type')
                    ->label('Type de bien')
                    ->options([
                        'materiel'   => 'Matériel',
                        'immobilier' => 'Immobilier',
                    ])->required()->native(false),

                Forms\Components\TextInput::make('category')
                    ->label('Catégorie')->placeholder('véhicule, terrain, bâtiment…')->maxLength(64),

                Forms\Components\TextInput::make('title')
                    ->label('Désignation')->required()->maxLength(150),

                Forms\Components\Textarea::make('description')
                    ->label('Description')->rows(3)->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Valeur & statut')->columns(3)->schema([
                Forms\Components\TextInput::make('estimated_value')
                    ->label('Valeur estimée')->numeric()->minValue(0),

                Forms\Components\Select::make('currency')
                    ->label('Devise')->options(['CDF'=>'CDF','USD'=>'USD'])->default('CDF')->native(false),

                Forms\Components\Select::make('status')
                    ->label('Statut')->options([
                        'active'            => 'Actif',
                        'under_maintenance' => 'En maintenance',
                        'disposed'          => 'Cédé / Sorti',
                    ])->default('active')->native(false),

                Forms\Components\DatePicker::make('acquired_at')->label('Date d’acquisition'),
                Forms\Components\DatePicker::make('disposed_at')->label('Date de cession'),
            ]),

            Forms\Components\Section::make('Localisation')->columns(3)->schema([
                Forms\Components\TextInput::make('address')->label('Adresse')->columnSpanFull(),

                Forms\Components\TextInput::make('province')->label('Province'),
                Forms\Components\TextInput::make('city')->label('Ville / Territoire'),
                Forms\Components\TextInput::make('country_code')->label('Pays')->default('CD')->maxLength(2),

                // Saisie simple des coordonnées + preview carte
                Forms\Components\TextInput::make('lat')->label('Latitude')->numeric()->step('0.0000001'),
                Forms\Components\TextInput::make('lng')->label('Longitude')->numeric()->step('0.0000001'),

                // Petite preview Leaflet (lecture seule) basée sur lat/lng saisis
                Forms\Components\View::make('forms.asset-map-preview')
                    ->label('Carte (aperçu)')
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get) => filled($get('lat')) && filled($get('lng'))),
            ]),

            Forms\Components\Section::make('Médias')->columns(1)->schema([
                Forms\Components\FileUpload::make('photos')
                    ->label('Photos')
                    ->disk('public')
                    ->directory('assets')
                    ->multiple()
                    ->downloadable()
                    ->image()
                    ->imageEditor()
                    ->reorderable()
                    ->hint('Tu peux charger plusieurs photos du bien.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photos.0')
                    ->label('Photo')->circular()->defaultImageUrl(asset('images/default.jpg'))->size(40),

                Tables\Columns\TextColumn::make('title')->label('Désignation')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('asset_type')->label('Type')
                    ->colors(['info' => 'materiel', 'success' => 'immobilier'])
                    ->formatStateUsing(fn($s) => $s === 'materiel' ? 'Matériel' : 'Immobilier'),

                Tables\Columns\TextColumn::make('category')->label('Catégorie')->toggleable(),

                Tables\Columns\TextColumn::make('province')->label('Province')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('city')->label('Ville')->toggleable(),

                Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                    'success' => 'active',
                    'warning' => 'under_maintenance',
                    'gray'    => 'disposed',
                ])->formatStateUsing(fn($s) => [
                    'active' => 'Actif',
                    'under_maintenance' => 'Maintenance',
                    'disposed' => 'Cédé',
                ][$s] ?? $s),

                Tables\Columns\TextColumn::make('estimated_value')->label('Valeur')
                    ->formatStateUsing(fn($v, $r) => $v ? number_format((float)$v,0,' ',' ').' '.($r->currency ?? 'CDF') : '—')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_type')->label('Type')->options([
                    'materiel'=>'Matériel','immobilier'=>'Immobilier'
                ]),
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options([
                    'active'=>'Actif','under_maintenance'=>'Maintenance','disposed'=>'Cédé',
                ]),
                Tables\Filters\SelectFilter::make('province')->label('Province')
                    ->options(fn() => \App\Models\VeteranAsset::query()
                        ->select('province')->whereNotNull('province')->distinct()->orderBy('province')->pluck('province','province')->toArray()),
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
            LogsRelationManager::class, // Journal de gestion
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVeteranAssets::route('/'),
            'create' => Pages\CreateVeteranAsset::route('/create'),
            'view'   => Pages\ViewVeteranAsset::route('/{record}'),
            'edit'   => Pages\EditVeteranAsset::route('/{record}/edit'),
        ];
    }
}
