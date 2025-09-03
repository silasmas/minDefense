<?php
namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Veteran;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use App\Filament\Exports\VeteranExporter;
use App\Filament\Imports\VeteranImporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Resources\VeteranResource\Pages;
use App\Filament\Resources\VeteranResource\RelationManagers\PaymentsRelationManager;

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
                    ->disk(fn($record) => $record->photo_disk ?? 'public')
                    ->getStateUsing(fn($record) => $record->photo_for_column) // 👈 utilise l'accessor
                    ->circular()
                    ->defaultImageUrl(asset('images/default.jpg'))
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
                    BulkAction::make('notify_month_sms')
                        ->label('Envoyer SMS pension (mois)')
                        ->icon('heroicon-m-megaphone')
                        ->form([
                            Forms\Components\DatePicker::make('period_month')
                                ->label('Mois')->required()->native(false),

                            Forms\Components\Select::make('mode')
                                ->label('Mode')->options([
                                'resume' => 'Vrac (total par vétéran)',
                                'detail' => 'Détaillé (toutes lignes)',
                            ])->default('resume')->required()->native(false),

                            Forms\Components\Textarea::make('template')
                                ->label('Modèle de message')->rows(4)->required()
                                ->default("Bonjour {prenom} {nom}, votre pension de {mois} est de {montant_total} {devise}. {details}")
                                ->helperText(<<<'HTML'
Variables : <strong>{prenom}</strong>, <strong>{nom}</strong>, <strong>{mois}</strong>, <strong>{montant_total}</strong>, <strong>{devise}</strong>, <strong>{details}</strong>, <strong>{matricule}</strong>, <strong>{carte}</strong>
HTML),
                        ])

                    /* ---------- BOUTON : PRÉVISUALISER (ne fait qu’afficher un aperçu) ---------- */
                        ->extraModalFooterActions([
                            Tables\Actions\Action::make('preview')
                                ->label('Prévisualiser')
                                ->icon('heroicon-m-eye')
                                ->color('gray')
                                ->action(function(Collection$records,array$data){

                                    /* helpers pour rendu + métriques SMS */
                                    $fmtMoney=fn(float$n)=>number_format($n,0,' ',' ');

                                    $render = function (string $tpl, array $ctx): string {
                                        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($ctx) {
                                            $k = $m[1];
                                            return array_key_exists($k, $ctx) ? (string) $ctx[$k] : $m[0];
                                        }, $tpl);
                                    };

                                    // Détection GSM-7 / UCS-2 + calcul segments
                                    $isGsm7 = function (string $text) {
                                        $gsm     = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
                                        $ext     = "^{}\\[~]|€";
                                        $allowed = $gsm . $ext;
                                        // Tous les caractères doivent appartenir au set autorisé (ou être \n \r)
                                        $len = mb_strlen($text, 'UTF-8');
                                        for ($i = 0; $i < $len; $i++) {
                                            $ch = mb_substr($text, $i, 1, 'UTF-8');
                                            if (! str_contains($allowed, $ch)) {
                                                return false;
                                            }
                                        }
                                        return true;
                                    };

                                    $smsMetrics = function (string $text) use ($isGsm7) {
                                        $extChars = ['^', '{', '}', '\\', '[', '~', ']', '|', '€']; // comptent 2 septets en GSM7
                                        if ($isGsm7($text)) {
                                            $len = 0;
                                            $L   = mb_strlen($text, 'UTF-8');
                                            for ($i = 0; $i < $L; $i++) {
                                                $ch = mb_substr($text, $i, 1, 'UTF-8');
                                                $len += in_array($ch, $extChars, true) ? 2 : 1; // septets
                                            }
                                            $segments = $len <= 160 ? 1 : (int) ceil($len / 153);
                                            return ['GSM-7', $L, $segments]; // on retourne nb de caractères “visibles” + segments
                                        } else {
                                            $L        = mb_strlen($text, 'UTF-8'); // UCS-2: 70/67
                                            $segments = $L <= 70 ? 1 : (int) ceil($L / 67);
                                            return ['UCS-2', $L, $segments];
                                        }
                                    };

                                    /* ---- enregistrements sélectionnés & vérifs ---- */
                                    if ($records->isEmpty()) {
                                        Notification::make()->title('Sélection vide')->danger()->send();
                                        return;
                                    }
                                    if (empty($data['period_month'])) {
                                        Notification::make()->title('Choisis d’abord le mois')->danger()->send();
                                        return;
                                    }

                                    $month = Carbon::parse($data['period_month'])->startOfMonth();
                                    $mode  = $data['mode'] ?? 'resume';
                                    $tpl   = trim($data['template'] ?? '');

                                    // On prend le premier vétéran sélectionné comme EXEMPLE
                                    /** @var \App\Models\Veteran|null $vet */
                                    $vet = $records->first();
                                    if (! $vet) {
                                        Notification::make()->title('Pas de vétéran')->danger()->send();
                                        return;
                                    }

                                    // Récupérer ses lignes pour le mois
                                    $rows = $vet->payments()->whereDate('period_month', $month)->orderBy('period_month')->get();
                                    if ($rows->isEmpty()) {
                                        Notification::make()->title("Aucune ligne pour {$month->format('m/Y')}")->warning()->send();
                                        return;
                                    }

                                    $cur     = $rows->first()->currency ?? 'CDF';
                                    $total   = (float) $rows->sum('amount');
                                    $details = $mode === 'detail'
                                    ? $rows->map(fn($r) => $fmtMoney((float) $r->amount) . " {$cur}")->implode(' + ')
                                    : '';

                                    $ctx = [
                                        'prenom'        => $vet->firstname ?? '',
                                        'nom'           => $vet->lastname ?? '',
                                        'mois'          => $month->format('m/Y'),
                                        'montant_total' => $fmtMoney($total),
                                        'devise'        => $cur,
                                        'details'       => $details,
                                        'matricule'     => $vet->service_number ?? '',
                                        'carte'         => $vet->card_number ?? '',
                                    ];

                                    $msg                  = $render($tpl, $ctx);
                                    [$enc, $chars, $segs] = $smsMetrics($msg);

                                    Notification::make()
                                        ->title('Aperçu SMS')
                                        ->body(
                                            "Vers: {$vet->phone}\n\n" .
                                            $msg . "\n\n" .
                                            "Compteur: {$chars} caractères — {$segs} SMS ({$enc})"
                                        )
                                        ->persistent()
                                        ->success()
                                        ->send();
                                }),
                        ])

                    /* ---------- ENVOI EFFECTIF ---------- */
                        ->action(function (Collection $records, array $data) {
                            $month = Carbon::parse($data['period_month'])->startOfMonth();
                            $mode  = $data['mode'] ?? 'resume';
                            $tpl   = trim($data['template'] ?? '');

                            $fmtMoney = fn(float $n) => number_format($n, 0, ' ', ' ');

                            $render = function (string $tpl, array $ctx): string {
                                return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($ctx) {
                                    return array_key_exists($m[1], $ctx) ? (string) $ctx[$m[1]] : $m[0];
                                }, $tpl);
                            };

                            $sent = 0; $skipped = 0;

                            foreach ($records as $vet) {
                                /** @var \App\Models\Veteran $vet */
                                if (! $vet->phone) {$skipped++;continue;}

                                $rows = $vet->payments()->whereDate('period_month', $month)->orderBy('period_month')->get();
                                if ($rows->isEmpty()) {$skipped++;continue;}

                                $cur     = $rows->first()->currency ?? 'CDF';
                                $total   = (float) $rows->sum('amount');
                                $details = $mode === 'detail'
                                ? $rows->map(fn($r) => $fmtMoney((float) $r->amount) . " {$cur}")->implode(' + ')
                                : '';

                                $ctx = [
                                    'prenom'        => $vet->firstname ?? '',
                                    'nom'           => $vet->lastname ?? '',
                                    'mois'          => $month->format('m/Y'),
                                    'montant_total' => $fmtMoney($total),
                                    'devise'        => $cur,
                                    'details'       => $details,
                                    'matricule'     => $vet->service_number ?? '',
                                    'carte'         => $vet->card_number ?? '',
                                ];

                                $msg = $render($tpl, $ctx);
                                App::make(\App\Services\SmsSender::class)->send($vet->phone, $msg);
                                $sent++;
                            }

                            Notification::make()
                                ->title("SMS envoyés : {$sent} • Ignorés : {$skipped}")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('cartes_pdf')
                        ->label('Cartes PDF (sélection)')
                        ->icon('heroicon-m-identification')
                        ->action(function (Collection $records) {
                            $html = view('pdf.veteran-card-sheet', ['veterans' => $records])->render();
                            $pdf  = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                            return response()->streamDownload(fn() => print($pdf->output()), 'cartes.pdf');
                        }),
                    Tables\Actions\BulkAction::make('export_selected_csv')
                        ->label('Exporter sélection (CSV)')
                        ->icon('heroicon-m-document-arrow-down')
                        ->action(function (Collection $records) {
                            $ids     = $records->pluck('id')->all();
                            $headers = [
                                'firstname', 'lastname', 'middlename', 'gender', 'birthdate', 'birthplace',
                                'service_number', 'nin', 'branch', 'rank', 'phone', 'email', 'address',
                                'status', 'card_number', 'card_status', 'card_expires_at',
                            ];

                            return response()->streamDownload(function () use ($headers, $ids) {
                                $out = fopen('php://output', 'w');
                                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
                                fputcsv($out, $headers);

                                \App\Models\Veteran::whereIn('id', $ids)
                                    ->orderBy('lastname')->orderBy('firstname')
                                    ->chunk(1000, function ($chunk) use ($out) {
                                        foreach ($chunk as $v) {
                                            fputcsv($out, [
                                                $v->firstname, $v->lastname, $v->middlename, $v->gender,
                                                optional($v->birthdate)->format('Y-m-d'), $v->birthplace,
                                                $v->service_number, $v->nin, $v->branch, $v->rank,
                                                $v->phone, $v->email, $v->address,
                                                $v->status, $v->card_number, $v->card_status,
                                                optional($v->card_expires_at)->format('Y-m-d'),
                                            ]);
                                        }
                                    });

                                fclose($out);
                            }, 'veterans-selection.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(VeteranImporter::class),
                ExportAction::make()
                    ->exporter(VeteranExporter::class) ->formats([
        ExportFormat::Xlsx,
        ExportFormat::Csv,
    ]),
                // Tables\Actions\Action::make('import_csv')
                //     ->label('Importer CSV')
                //     ->icon('heroicon-m-arrow-up-tray')
                //     ->form([
                //         Forms\Components\FileUpload::make('csv')
                //             ->label('Fichier CSV')
                //             ->acceptedFileTypes(['text/csv', '.csv', 'text/plain'])
                //             ->required()
                //             ->disk('local')    // storage/app
                //             ->directory('tmp') // storage/app/tmp
                //             ->visibility('private')
                //             ->preserveFilenames() // utile pour déboguer
                //             ->helperText('Colonnes attendues : firstname, lastname, middlename, gender, birthdate (YYYY-MM-DD), birthplace, service_number, nin, branch, rank, phone, email, address, status (draft/recognized/suspended/deceased), card_number, card_status (active/revoked/lost), card_expires_at (YYYY-MM-DD)'),
                //     ])
                //     ->action(function (array $data) {
                //         $path = Storage::disk('local')->path($data['csv']);

                //         // Helpers
                //         $parseDate = function ($val) {
                //             if ($val === null || $val === '') {
                //                 return null;
                //             }

                //             try {return Carbon::parse($val);} catch (\Throwable) {return null;}
                //         };
                //         $normalizePhone = function (?string $s) {
                //             if (! $s) {
                //                 return null;
                //             }

                //             $s = preg_replace('/\D+/', '', $s);
                //             if (Str::startsWith($s, '0')) {
                //                 $s = '243' . substr($s, 1);
                //             }

                //             if (! Str::startsWith($s, '243')) {
                //                 $s = '243' . $s;
                //             }

                //             return '+' . $s;
                //         };
                //         // Mappings FR/variantes => codes internes
                //         $mapStatus = function (?string $s) {
                //             $s = Str::lower(trim((string) $s));
                //             return match ($s) {
                //                 'reconnu', 'reconue', 'recognized' => 'recognized',
                //                 'brouillon', 'draft'    => 'draft',
                //                 'suspendu', 'suspended' => 'suspended',
                //                 'decede', 'décédé', 'deceased'     => 'deceased',
                //                 default => 'recognized',
                //             };
                //         };
                //         $mapCard = function (?string $s) {
                //             $s = Str::lower(trim((string) $s));
                //             return match ($s) {
                //                 'actif', 'active'       => 'active',
                //                 'revoquee', 'révoquée', 'revoked'  => 'revoked',
                //                 'perdue', 'lost'        => 'lost',
                //                 default => 'active',
                //             };
                //         };

                //         $created = 0; $updated = 0; $skipped = 0; $errors = [];

                //         // Lecture CSV (header à la 1ère ligne)
                //         $csv = Reader::createFromPath($path, 'r');
                //         $csv->setHeaderOffset(0);
                //         foreach ($csv->getRecords() as $rowIndex => $r) {
                //             try {
                //                 $sn = trim($r['service_number'] ?? '');
                //                 if ($sn === '') {
                //                     $skipped++;
                //                     $errors[] = 'Ligne ' . ($rowIndex + 1) . ': matricule (service_number) manquant.';
                //                     continue;
                //                 }

                //                 $attrs = [
                //                     'firstname'       => $r['firstname'] ?? null,
                //                     'lastname'        => $r['lastname'] ?? null,
                //                     'middlename'      => $r['middlename'] ?? null,
                //                     'gender'          => $r['gender'] ?? null,
                //                     'birthdate'       => $parseDate($r['birthdate'] ?? null),
                //                     'birthplace'      => $r['birthplace'] ?? null,
                //                     'nin'             => $r['nin'] ?? null,
                //                     'branch'          => $r['branch'] ?? null,
                //                     'rank'            => $r['rank'] ?? null,
                //                     'phone'           => $normalizePhone($r['phone'] ?? null),
                //                     'email'           => $r['email'] ?? null,
                //                     'address'         => $r['address'] ?? null,
                //                     'status'          => $mapStatus($r['status'] ?? null),
                //                     'card_number'     => $r['card_number'] ?? null,
                //                     'card_status'     => $mapCard($r['card_status'] ?? null),
                //                     'card_expires_at' => $parseDate($r['card_expires_at'] ?? null),
                //                 ];

                //                 $v = \App\Models\Veteran::firstOrNew(['service_number' => $sn]);
                //                 $v->fill($attrs);

                //                 if (! $v->exists) {
                //                     $v->save();
                //                     $created++;
                //                 } else {
                //                     if ($v->isDirty()) {$updated++;}
                //                     $v->save();
                //                 }
                //             } catch (\Throwable $e) {
                //                 $skipped++;
                //                 $errors[] = 'Ligne ' . ($rowIndex + 1) . ': ' . $e->getMessage();
                //             }
                //         }

                //         Storage::disk('local')->delete($data['csv']);

                //         $title = "Import CSV terminé — créés: {$created}, mis à jour: {$updated}, ignorés: {$skipped}";
                //         $notif = Notification::make()->title($title)->success();
                //         if ($errors) {
                //             $notif->body(collect($errors)->take(10)->implode("\n") . (count($errors) > 10 ? "\n..." : ''))
                //                 ->persistent();
                //         }
                //         $notif->send();
                //     }),
                // Tables\Actions\Action::make('export_all_csv')
                //     ->label('Exporter tous (CSV)')
                //     ->icon('heroicon-m-arrow-down-tray')
                //     ->action(function () {
                //         $headers = [
                //             'firstname', 'lastname', 'middlename', 'gender', 'birthdate', 'birthplace',
                //             'service_number', 'nin', 'branch', 'rank', 'phone', 'email', 'address',
                //             'status', 'card_number', 'card_status', 'card_expires_at',
                //         ];

                //         $q = \App\Models\Veteran::query()->orderBy('lastname')->orderBy('firstname');

                //         return response()->streamDownload(function () use ($headers, $q) {
                //             $out = fopen('php://output', 'w');
                //             fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
                //             fputcsv($out, $headers);
                //             $q->chunk(1000, function ($chunk) use ($out) {
                //                 foreach ($chunk as $v) {
                //                     fputcsv($out, [
                //                         $v->firstname,
                //                         $v->lastname,
                //                         $v->middlename,
                //                         $v->gender,
                //                         optional($v->birthdate)->format('Y-m-d'),
                //                         $v->birthplace,
                //                         $v->service_number,
                //                         $v->nin,
                //                         $v->branch,
                //                         $v->rank,
                //                         $v->phone,
                //                         $v->email,
                //                         $v->address,
                //                         $v->status,
                //                         $v->card_number,
                //                         $v->card_status,
                //                         optional($v->card_expires_at)->format('Y-m-d'),
                //                     ]);
                //                 }
                //             });
                //             fclose($out);
                //         }, 'veterans.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                //     }),
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
            // VeteranCasesRelationManager::class,
            // VeteranPaymentsRelationManager::class,
            // StatusHistoryRelationManager::class, // <—
            PaymentsRelationManager::class,
        ];
    }

}
