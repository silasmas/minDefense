<?php
namespace App\Filament\Resources;

use App\Filament\Resources\VeteranResource\Pages;
use App\Filament\Resources\VeteranResource\RelationManagers\StatusHistoryRelationManager;
use App\Filament\Resources\VeteranResource\RelationManagers\VeteranCasesRelationManager;
use App\Filament\Resources\VeteranResource\RelationManagers\VeteranPaymentsRelationManager;
use App\Models\Veteran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class VeteranResource extends Resource
{
    protected static ?string $model           = Veteran::class;
    protected static ?string $navigationIcon  = 'heroicon-m-user-group';
    protected static ?string $navigationLabel = 'Anciens combattants';
    protected static ?string $modelLabel      = 'Ancien combattant';
    protected static ?string $navigationGroup = 'Social & Défense';
    protected static ?int $navigationSort     = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('lastname')
                        ->label('Nom')->required()->maxLength(100),
                    Forms\Components\TextInput::make('firstname')
                        ->label('Prénom')->required()->maxLength(100),
                    Forms\Components\DatePicker::make('birthdate')->label('Date de naissance'),

                    Forms\Components\Select::make('gender')->label('Sexe')
                        ->options(['male' => 'Homme', 'female' => 'Femme', 'other' => 'Autre'])
                        ->native(false),

                    Forms\Components\TextInput::make('service_number')
                        ->label('Matricule')->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('nin')
                        ->label('NIN')->unique(ignoreRecord: true),
                ]),

            Forms\Components\Section::make('Coordonnées')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('phone')->label('Téléphone'),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('address')->label('Adresse')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Service')
                ->columns(3)
                ->schema([

                    Forms\Components\TextInput::make('card_number')->label('N° carte'),
                    Forms\Components\DatePicker::make('card_expires_at')->label('Expiration'),
                    Forms\Components\Select::make('card_status')->label('Statut carte')
                        ->options(['active' => 'Active', 'revoked' => 'Révoquée', 'lost' => 'Perdue'])
                        ->native(false),
                    Forms\Components\Textarea::make('card_status_reason')->label('Motif')->columnSpanFull(),

                    Forms\Components\TextInput::make('branch')->label('Branche (armée)'),
                    Forms\Components\TextInput::make('rank')->label('Grade'),
                    Forms\Components\DatePicker::make('service_start_date')->label('Début service'),
                    Forms\Components\DatePicker::make('service_end_date')->label('Fin service'),
                    Forms\Components\Select::make('status')->label('Statut')
                        ->options([
                            'draft'      => 'Brouillon',
                            'recognized' => 'Reconnu',
                            'suspended'  => 'Suspendu',
                            'deceased'   => 'Décédé',
                        ])->native(false)->required(),
                    Forms\Components\Textarea::make('notes')->label('Notes')->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Photo')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('photo_path')
                        ->label('Photo')
                        ->disk(fn() => 'public')
                        ->directory('veterans/photos')
                        ->image()
                        ->imageEditor()
                        ->visibility('public')
                        ->openable()
                        ->downloadable()
                        ->imageEditorAspectRatios(['1:1', '3:4', '4:3'])
                        ->maxSize(2048),
                    Forms\Components\Hidden::make('photo_disk')->default('public'),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('lastname')
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('card_number')->label('N° carte')->toggleable(),
                Tables\Columns\TextColumn::make('card_expires_at')->date()->label('Expire')->toggleable(),
                Tables\Columns\BadgeColumn::make('card_status')
                    ->label('Statut carte')
                    ->formatStateUsing(fn(?string $state) => [
                        'active'  => 'Active',
                        'revoked' => 'Révoquée',
                        'lost'    => 'Perdue',
                        null      => '—',
                    ][$state] ?? ucfirst((string) $state))
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'revoked',
                        'warning' => 'lost',
                    ])
                    ->icons([
                        'heroicon-m-check-badge'          => 'active',
                        'heroicon-m-no-symbol'            => 'revoked',
                        'heroicon-m-question-mark-circle' => 'lost',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->formatStateUsing(fn(Veteran $r) => $r->full_name)
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('lastname', 'like', "%{$search}%")
                                ->orWhere('firstname', 'like', "%{$search}%")
                                ->orWhere('service_number', 'like', "%{$search}%")
                                ->orWhere('nin', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_number')->label('Matricule')->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('nin')->label('NIN')->toggleable()->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn(string $state) => [
                        'draft'      => 'Brouillon',
                        'recognized' => 'Reconnu',
                        'suspended'  => 'Suspendu',
                        'deceased'   => 'Décédé',
                    ][$state] ?? ucfirst($state))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'recognized',
                        'danger'  => 'suspended',
                        'gray'    => 'deceased',
                    ])
                    ->icons([
                        'heroicon-m-clock'  => 'draft',
                        'heroicon-m-check'  => 'recognized',
                        'heroicon-m-pause'  => 'suspended',
                        'heroicon-m-x-mark' => 'deceased',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch')->label('Branche')->toggleable(),
                Tables\Columns\TextColumn::make('rank')->label('Grade')->toggleable(),
                Tables\Columns\TextColumn::make('service_start_date')->date()->label('Début'),
                Tables\Columns\TextColumn::make('service_end_date')->date()->label('Fin'),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Créé le')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft'      => 'Brouillon',
                        'recognized' => 'Reconnu',
                        'suspended'  => 'Suspendu',
                        'deceased'   => 'Décédé',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Tables\Actions\BulkAction::make('notify_month_sms')
                        ->label('Envoyer SMS pension (mois)')
                        ->icon('heroicon-m-megaphone')
                        ->form([
                            Forms\Components\DatePicker::make('period_month')->label('Mois')->required(),
                            Forms\Components\Select::make('mode')->label('Mode')->options([
                                'resume' => 'Vrac (total par vétéran)',
                                'detail' => 'Détaillé (toutes lignes)',
                            ])->default('resume')->required()->native(false),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $month = Carbon::parse($data['period_month'])->startOfMonth();
                            foreach ($records as $vet) {
                                /** @var \App\Models\Veteran $vet */
                                if (! $vet->phone) {
                                    continue;
                                }

                                $rows = $vet->payments()->whereDate('period_month', $month)->orderBy('period_month')->get();
                                if ($rows->isEmpty()) {
                                    continue;
                                }

                                $cur = $rows->first()->currency ?? 'CDF';
                                if ($data['mode'] === 'resume') {
                                    $total = (float) $rows->sum('amount');
                                    $msg   = "Pension " . $month->format('m/Y') . ": " . number_format($total, 0, ' ', ' ') . " {$cur}.";
                                } else {
                                    $msg = "Pension " . $month->format('m/Y') . ": " .
                                    $rows->map(fn($r) => number_format((float) $r->amount, 0, ' ', ' ') . " {$cur}")
                                        ->implode(' + ') . ".";
                                }
                                app(\App\Services\SmsSender::class)->send($vet->phone, $msg);
                            }
                            \Filament\Notifications\Notification::make()->title('SMS envoi en cours')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('cartes_pdf')
                        ->label('Cartes PDF (sélection)')
                        ->icon('heroicon-m-identification')
                        ->action(function (Collection $records) {
                            $html = view('pdf.veteran-card-sheet', ['veterans' => $records])->render();
                            $pdf  = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                            return response()->streamDownload(fn() => print($pdf->output()), 'cartes.pdf');
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import_csv')
                    ->label('Importer CSV')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('csv')
                            ->label('Fichier CSV')
                            ->acceptedFileTypes(['text/csv', '.csv', 'text/plain'])
                            ->required()
                            ->disk('local')
                            ->directory('tmp') // storage/app/tmp
                            ->visibility('private'),
                    ])
                    ->action(function (array $data) {
                        $path = Storage::disk('local')->path($data['csv']);
                        $csv  = Reader::createFromPath($path, 'r');
                        $csv->setHeaderOffset(0);

                        foreach ($csv->getRecords() as $r) {
                            \App\Models\Veteran::updateOrCreate(
                                ['service_number' => $r['service_number'] ?? null],
                                [
                                    'firstname'       => $r['firstname'] ?? null,
                                    'lastname'        => $r['lastname'] ?? null,
                                    'birthdate'       => $r['birthdate'] ?? null, // YYYY-mm-dd
                                    'gender'          => $r['gender'] ?? null,
                                    'phone'           => $r['phone'] ?? null,
                                    'email'           => $r['email'] ?? null,
                                    'address'         => $r['address'] ?? null,
                                    'branch'          => $r['branch'] ?? null,
                                    'rank'            => $r['rank'] ?? null,
                                    'status'          => $r['status'] ?? 'recognized',
                                    'card_number'     => $r['card_number'] ?? null,
                                    'card_expires_at' => $r['card_expires_at'] ?? null,
                                    'card_status'     => $r['card_status'] ?? null,
                                ]
                            );
                        }

                        Storage::disk('local')->delete($data['csv']);

                        Notification::make()
                            ->title('Import CSV terminé')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVeterans::route('/'),
            'create' => Pages\CreateVeteran::route('/create'),
            'view'   => Pages\ViewVeteran::route('/{record}'),
            'edit'   => Pages\EditVeteran::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['lastname', 'firstname', 'service_number', 'nin', 'phone', 'email'];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Profil')
                ->columns(4)
                ->schema([
                    Infolists\Components\ImageEntry::make('photo_path')
                        ->disk(fn($record) => $record->photo_disk ?? 'public')
                        ->circular()
                        ->columnSpan(1),
                    Infolists\Components\TextEntry::make('full_name')
                        ->label('Nom complet')->columnSpan(3)->weight('bold')->size('lg'),
                    Infolists\Components\TextEntry::make('service_number')->label('Matricule'),
                    Infolists\Components\TextEntry::make('nin')->label('NIN'),
                    Infolists\Components\TextEntry::make('branch')->label('Branche'),
                    Infolists\Components\TextEntry::make('rank')->label('Grade'),

                ]),
            Infolists\Components\Section::make('Résumé')
                ->columns(2)
                ->schema([
                    // 5 derniers statuts (toutes affaires confondues)
                    Infolists\Components\ViewEntry::make('last_statuses')
                        ->view('infolists.veteran-last-statuses')
                        ->state(fn(\App\Models\Veteran $r) =>
                            \App\Models\CaseStatusHistory::with('case')
                                ->whereHas('case', fn($q) => $q->where('veteran_id', $r->id))
                                ->orderByDesc('set_at')
                                ->limit(10)
                                ->get()
                        ),

                    // 6 derniers paiements
                    Infolists\Components\ViewEntry::make('last_payments')
                        ->view('infolists.veteran-last-payments')
                        ->state(fn(Veteran $r) => $r->payments()
                                ->orderByDesc('paid_at')
                                ->orderByDesc('id')
                                ->limit(6)
                                ->get()
                        ),
                ]),

        ]);
    }
    public static function getRelations(): array
    {
        return [
            VeteranCasesRelationManager::class,
            VeteranPaymentsRelationManager::class,
            StatusHistoryRelationManager::class, // <—
        ];
    }

}
