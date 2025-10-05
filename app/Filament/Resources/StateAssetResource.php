<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StateAssetResource\Pages;
use App\Filament\Resources\StateAssetResource\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\StateAssetResource\RelationManagers\LogsRelationManager;
use App\Models\StateAsset;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
// use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\View as ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

// use App\Filament\Resources\Infolists\Infolist;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StateAssetResource extends Resource
{
    protected static ?string $model = StateAsset::class;

    protected static ?string $navigationIcon   = 'heroicon-m-cube';
    protected static ?string $navigationGroup  = 'Patrimoine de l’État';
    protected static ?string $modelLabel       = 'Bien de l’État';
    protected static ?string $pluralModelLabel = 'Biens de l’État';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identification')
                ->columns(3)
                ->schema([
                    Select::make('asset_type')
                        ->label('Type de bien')
                        ->options(['materiel' => 'Matériel', 'immobilier' => 'Immobilier'])
                        ->required()
                        ->live() // pour réagir côté front
                        ->native(false)
                        ->helperText('Choisissez la nature du bien. « Matériel » (véhicule, ordinateur, etc.) ou « Immobilier » (parcelle, bâtiment, entrepôt, etc.).'),

                    TextInput::make('asset_code')
                        ->label('Code inventaire')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true, table: 'state_assets', column: 'asset_code'), // unicité
                    Select::make('material_category')
                        ->label('Catégorie matériel')
                        ->options([
                            'vehicle'   => 'Véhicule',
                            'computer'  => 'Informatique',
                            'furniture' => 'Mobilier',
                            'medical'   => 'Médical',
                        ])
                        ->visible(fn($get) => $get('asset_type') === 'materiel'),
                    FileUpload::make('material_image_path')
                        ->label('Image du matériel (optionnel)')
                        ->image()
                        ->directory('materials')
                        ->visible(fn($get) => $get('asset_type') === 'materiel'),
                    TextInput::make('category')
                        ->label('Catégorie')
                        ->maxLength(64)
                        ->helperText('Famille fonctionnelle du bien (ex. « Véhicule », « Informatique », « Terrain »).'),

                    TextInput::make('title')
                        ->label('Désignation')
                        ->required()
                        ->maxLength(150)
                        ->helperText('Titre court et explicite du bien (ex. « Toyota Hilux 2.4D 2020 » ou « Parcelle UPN/15 »).'),

                    TextInput::make('serial_number')
                        ->label('N° série (si matériel)')
                        ->maxLength(120)
                        ->helperText('Numéro de série/IMEI/châssis du matériel. Laissez vide pour un bien immobilier.'),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Détails complémentaires : état, caractéristiques, remarques, références internes…'),
                ])->columns(2),

            Section::make('Valeur & statut')
                ->columns(3)
                ->schema([
                    TextInput::make('estimated_value')
                        ->label('Valeur estimée')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Montant estimatif du bien pour le suivi patrimonial et les rapports (sans séparateur de milliers).'),

                    Select::make('currency')
                        ->label('Devise')
                        ->options(['CDF' => 'CDF', 'USD' => 'USD'])
                        ->default('CDF')
                        ->native(false)
                        ->helperText('Devise utilisée pour la valeur estimée.'),

                    Select::make('status')
                        ->label('Statut')
                        ->options([
                            'active'            => 'Actif',
                            'under_maintenance' => 'En maintenance',
                            'disposed'          => 'Cédé / Sorti',
                        ])
                        ->default('active')
                        ->native(false)
                        ->helperText('État de vie du bien : en service (Actif), en réparation (Maintenance), ou sorti du patrimoine (Cédé).'),

                    DatePicker::make('acquired_at')
                        ->label('Date d’acquisition')
                        ->helperText('Date d’entrée du bien dans le patrimoine (achat, don, transfert…).'),

                    DatePicker::make('disposed_at')
                        ->label('Date de cession')
                        ->helperText('Renseignez uniquement si le bien a été cédé / désaffecté / détruit.'),

                    TextInput::make('managing_agency')
                        ->label('Structure gestionnaire')
                        ->maxLength(150)
                        ->helperText('Entité administrative responsable du bien (ex. « Direction Logistique », « Antenne Provinciale Nord-Kivu »).'),
                ]),
            Section::make('Géolocalisation')
                ->schema([
                    Group::make()->schema([
                        TextInput::make('lat')->label('Latitude')->numeric()->step('any'),
                        TextInput::make('lng')->label('Longitude')->numeric()->step('any'),
                        TextInput::make('extent_side_m')
                            ->label('Côté du carré (m)')
                            ->numeric()
                            ->minValue(10)
                            ->hint('Utilisé pour tracer l’emprise IMMOBILIÈRE'),
                    ])->columns(3),

                    // La carte interactive
                    ViewField::make('asset_map_preview')
                        ->label('Carte (aperçu)')
                        ->view('forms.asset-map-preview') // ta blade d’aperçu
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => filled($get('lat')) && filled($get('lng'))),
                ]),
            Section::make('Localisation')
                ->columns(3)
                ->schema([
                    TextInput::make('address')
                        ->label('Adresse')
                        ->columnSpanFull()
                        ->helperText('Adresse postale ou repères de localisation (quartier, avenue, n° parcelle, commune…).'),

                    TextInput::make('province')
                        ->label('Province')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Exemple : vider la ville si la province change
                            $set('city', null);
                        })->helperText('Province, état ou région. Exemple : Kinshasa, Nord-Kivu, Sud-Ubangi…'),
                    TextInput::make('city')
                        ->label('Ville')
                        ->helperText('Ville/territoire. Exemple : Kinshasa, Goma, Mbandaka…'),

                    TextInput::make('country_code')
                        ->label('Pays')
                        ->default('CD')
                        ->maxLength(2)
                        ->helperText('Code pays ISO-2 (ex. « CD » pour RDC).'),

                    TextInput::make('lat')
                        ->label('Latitude')
                        ->numeric()
                        ->rule('nullable')
                        ->rule('between:-90,90')
                        ->helperText('Entre −90 et 90. Ex: -4.32 (Kinshasa).'),

                    TextInput::make('lng')
                        ->label('Longitude')
                        ->numeric()
                        ->rules([
                            'nullable',
                            'between:-180,180',
                            // Valider que lat et lng sont fournis ensemble
                            fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                $lat = $get('lat');
                                $lng = $value;
                                if (filled($lat) xor filled($lng)) {
                                    $fail('Renseignez la latitude ET la longitude (les deux champs).');
                                }
                            },
                        ])
                        ->helperText('Entre −180 et 180. Ex: 15.31 (Kinshasa).'),
                    View::make('forms.asset-map-preview')
                        ->label('Carte (aperçu)')
                        ->columnSpanFull()
                        ->visible(fn(Forms\Get $get) => filled($get('lat')) && filled($get('lng'))),
                ]),

            Section::make('Médias')
                ->schema([
                    FileUpload::make('photos')
                        ->label('Photos')
                        ->disk('public')
                        ->directory('assets')
                        ->multiple()
                        ->image()
                        ->imageEditor()
                        ->reorderable()
                        ->downloadable()
                        ->helperText('Ajoutez une ou plusieurs photos (JPEG/PNG). Elles seront stockées dans « storage/app/public/assets ».'),
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
                    'info' => 'materiel', 'success' => 'immobilier',
                ])->formatStateUsing(fn($state) => $state === 'materiel' ? 'Matériel' : 'Immobilier'),

                Tables\Columns\TextColumn::make('category')->label('Catégorie')->toggleable(),
                Tables\Columns\TextColumn::make('province')->label('Province')->badge()->toggleable(),

                Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                    'success' => 'active', 'warning' => 'under_maintenance', 'gray' => 'disposed',
                ])->formatStateUsing(fn($state) => [
                    'active' => 'Actif', 'under_maintenance' => 'Maintenance', 'disposed' => 'Cédé',
                ][$state] ?? $state),

                Tables\Columns\TextColumn::make('estimated_value')->label('Valeur')
                    ->formatStateUsing(fn($state, $record) => $state ? number_format((float) $state, 0, ' ', ' ') . ' ' . ($record->currency ?? 'CDF') : '—')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_type')->label('Type')
                    ->options(['materiel' => 'Matériel', 'immobilier' => 'Immobilier']),
                Tables\Filters\SelectFilter::make('status')->label('Statut')
                    ->options(['active' => 'Actif', 'under_maintenance' => 'Maintenance', 'disposed' => 'Cédé']),
                Tables\Filters\SelectFilter::make('province')->label('Province')
                    ->options(fn() => \App\Models\StateAsset::query()
                            ->select('province')->whereNotNull('province')->distinct()->orderBy('province')
                            ->pluck('province', 'province')->toArray()),
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
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Détails')
                ->schema([
                    TextEntry::make('name')->label('Intitulé'),
                    TextEntry::make('asset_type')->label('Type'),
                    TextEntry::make('material_category')
                        ->label('Catégorie matériel')
                        ->visible(fn($record) => $record->asset_type === 'materiel'),
                ])->columns(3),

            InfoSection::make('Localisation')
                ->schema([
                    ViewEntry::make('map_view')
                        ->label(false)
                        ->view('filament.assets.map-view'), // blade ci-dessous
                ])->columnSpanFull(),
            InfoSection::make('Localisation')
                ->schema([
                    ViewEntry::make('map_explorer')
                        ->label(false)
                        ->view('filament.assets.map-explorer'), // <-- nouvelle vue (point 4)
                ])->columnSpanFull(),

        ]);
    }
}
