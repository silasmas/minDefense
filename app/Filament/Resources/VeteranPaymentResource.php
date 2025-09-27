<?php

// app/Filament/Resources/VeteranPaymentResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\VeteranPaymentResource\Pages;
use App\Models\Veteran;
use App\Models\VeteranPayment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class VeteranPaymentResource extends Resource
{
    protected static ?string $model = VeteranPayment::class; // ✅ obligatoire

    public static function getEloquentQuery(): Builder
    {
        // ✅ garantit une query non-nulle et eager-load la relation
        return parent::getEloquentQuery()->with('veteran');
    }

    protected static ?string $navigationIcon  = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Paiements vétérans';
    protected static ?string $navigationGroup = 'Vétérans';
    protected static ?int $navigationSort     = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // == LIEN AU VÉTÉRAN ==
            Forms\Components\Select::make('veteran_id')
                ->label('Vétéran')
                ->relationship(name: 'veteran', titleAttribute: 'lastname')
                ->getOptionLabelFromRecordUsing(function (Veteran $v) {
                    $full = trim(($v->full_name ?? ($v->lastname . ' ' . $v->firstname)));
                    $mat  = $v->service_number ? " ({$v->service_number})" : '';
                    return $full . $mat;
                })
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Sélectionnez le vétéran bénéficiaire du paiement.'),

            // == (OPTIONNEL) LIEN À UN DOSSIER/CASE ==
            Forms\Components\Select::make('case_id')
                ->label('Dossier (optionnel)')
                ->relationship('case', 'id')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Associez ce paiement à un dossier si nécessaire.'),

            // == TYPE DE PAIEMENT ==
            Forms\Components\Select::make('payment_type')
                ->label('Type de paiement')
                ->options([
                    'pension' => 'Pension',
                    'arrears' => 'Arriéré',
                    'aid'     => 'Aide',
                ])
                ->default('pension')
                ->required()
                ->helperText('Catégorie du paiement (pension, arriéré, aide).'),

            // == PÉRIODE (MENSUELLE OU INTERVALLE) ==
            Forms\Components\DatePicker::make('period_month')
                ->label('Mois de référence')
                ->native(false)
                ->closeOnDateSelection()
                ->required()
                ->helperText('Pour les pensions mensuelles : la date sera enregistrée au 1er jour du mois.')
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('period_month', $state ? Carbon::parse($state)->startOfMonth()->toDateString() : null);
                })
                // --- Unicité composite: veteran_id + payment_type + period_month
                ->unique(
                    table: 'veteran_payments',
                    column: 'period_month',
                    ignorable: fn(?VeteranPayment $record)      => $record, // ignore en édition
                    modifyRuleUsing: fn(Unique $rule, Get $get) =>
                    $rule->where('veteran_id', $get('veteran_id'))
                        ->where('payment_type', $get('payment_type'))
                ),

            Forms\Components\Grid::make()->columns(2)->schema([
                Forms\Components\DatePicker::make('period_start')
                    ->label('Début de période')
                    ->helperText('Optionnel — utile pour les aides/arriérés couvrant un intervalle.'),

                Forms\Components\DatePicker::make('period_end')
                    ->label('Fin de période')
                    ->helperText('Optionnel — fin de l’intervalle couvert.')
                    ->rule(fn(Get $get) => function (string $attribute, $value, $fail) use ($get) {
                        $start = $get('period_start');
                        if ($start && $value && $value < $start) {
                            $fail('La fin de période doit être postérieure ou égale au début de période.');
                        }
                    }),
            ]),

            // == MONTANT & DEVISE ==
            Forms\Components\TextInput::make('amount')
                ->label('Montant')
                ->numeric()
                ->minValue(0.01)
                ->required()
                ->helperText('Exemple : 150.00'),

            Forms\Components\Select::make('currency')
                ->label('Devise')
                ->options(['USD' => 'USD', 'CDF' => 'CDF', 'EUR' => 'EUR'])
                ->default('USD')
                ->required()
                ->native(false)
                ->helperText('Code devise sur 3 caractères (ISO).'),

            // == STATUT & DATE DE PAIEMENT ==
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'scheduled' => 'Planifié',
                    'paid'      => 'Payé',
                    'failed'    => 'Échec',
                    'refunded'  => 'Remboursé',
                ])
                ->default('paid')
                ->required()
                ->helperText('État du paiement.'),

            Forms\Components\DateTimePicker::make('paid_at')
                ->label('Payé le')
                ->seconds(false)
                ->helperText('Renseignez la date/heure si le statut est « Payé ».')
                // Optionnel: forcer la présence de paid_at si Paid
                ->rule(fn(Get $get) => function (string $attribute, $value, $fail) use ($get) {
                    if (($get('status') === 'paid') && empty($value)) {
                        $fail('La date/heure de paiement est requise quand le statut est « Payé ».');
                    }
                }),

            // == RÉFÉRENCE & NOTES ==
            Forms\Components\TextInput::make('reference')
                ->label('Référence')
                ->maxLength(128)
                ->helperText('Référence bancaire / Mobile Money / pièce comptable. Si vide, elle sera générée automatiquement.'),

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->helperText('Informations complémentaires.'),
        ])->columns(2);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Vétéran: nom complet + matricule (depuis la relation)
                TextColumn::make('veteran.id')
                    ->label('Vétéran')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->veteran) {
                            return '—';
                        }

                        $n = $record->veteran->full_name ?: trim(($record->veteran->lastname ?? '') . ' ' . ($record->veteran->firstname ?? ''));
                        $m = $record->veteran->service_number ? " ({$record->veteran->service_number})" : '';
                        return trim($n . $m);
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        // Recherche sur lastname/firstname/matricule
                        $query->whereHas('veteran', function (Builder $q) use ($search) {
                            $q->where('lastname', 'like', "%{$search}%")
                                ->orWhere('firstname', 'like', "%{$search}%")
                                ->orWhere('service_number', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                // Type de paiement (badge)
                TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => [
                        'pension' => 'Pension',
                        'arrears' => 'Arriéré',
                        'aid'     => 'Aide',
                    ][$state] ?? ucfirst($state))
                    ->color(fn(string $state) => match ($state) {
                        'pension' => 'primary',
                        'arrears' => 'warning',
                        'aid'     => 'info',
                        default   => 'gray',
                    })
                    ->sortable(),

                // Mois de référence (affiche "Août 2025", stocké "2025-08-01")
                TextColumn::make('period_month')
                    ->label('Mois')
                    ->date('F Y')
                    ->sortable(),

                // Intervalle optionnel (début → fin)
                TextColumn::make('period_start')
                    ->label('Début')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_end')
                    ->label('Fin')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Montant + devise
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn($state, $record) => number_format((float) $state, 2, ',', ' ') . ' ' . ($record->currency ?? ''))
                    ->sortable(),

                // Statut (badge)
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => [
                        'scheduled' => 'Planifié',
                        'paid'      => 'Payé',
                        'failed'    => 'Échec',
                        'refunded'  => 'Remboursé',
                    ][$state] ?? ucfirst($state))
                    ->color(fn(string $state) => match ($state) {
                        'scheduled' => 'warning',
                        'paid'      => 'success',
                        'failed'    => 'danger',
                        'refunded'  => 'gray',
                        default     => 'gray',
                    })
                    ->sortable(),

                // Date/heure de paiement effectif
                TextColumn::make('paid_at')
                    ->label('Payé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                // Référence (banque / momo / pièce)
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                // Indicateur "notes présentes"
                IconColumn::make('has_notes')
                    ->label('Notes')
                    ->boolean()
                    ->getStateUsing(fn($record) => filled($record->notes))
                    ->tooltip('Ce paiement contient des notes.'),
            ])
            ->filters([
                // Filtre par type
                SelectFilter::make('payment_type')
                    ->label('Type')
                    ->options([
                        'pension' => 'Pension',
                        'arrears' => 'Arriéré',
                        'aid'     => 'Aide',
                    ]),
                // Filtre par statut
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'scheduled' => 'Planifié',
                        'paid'      => 'Payé',
                        'failed'    => 'Échec',
                        'refunded'  => 'Remboursé',
                    ]),
                // Filtre corbeille (SoftDeletes)
                TrashedFilter::make(),
                // Filtre par période de paiement (paid_at)
                Tables\Filters\Filter::make('paid_between')
                    ->label('Payé entre')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
                            ->when($data['to'] ?? null, fn($q, $v) => $q->whereDate('paid_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Marquer comme "Payé" (raccourci d’affectation)
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marquer payé')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->action(function (VeteranPayment $record) {
                        $record->update([
                            'status'  => 'paid',
                            'paid_at' => $record->paid_at ?? now(),
                        ]);
                    }),

                // Marquer "Échec"
                Tables\Actions\Action::make('mark_failed')
                    ->label('Marquer échec')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status !== 'failed')
                    ->action(fn(VeteranPayment $record) => $record->update(['status' => 'failed'])),

                // Marquer "Remboursé"
                Tables\Actions\Action::make('mark_refunded')
                    ->label('Rembourser')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status !== 'refunded')
                    ->action(fn(VeteranPayment $record) => $record->update(['status' => 'refunded'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_paid')
                    ->label('Marquer payés')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $r) {
                            $r->update(['status' => 'paid', 'paid_at' => $r->paid_at ?? now()]);
                        }
                    }),
                Tables\Actions\BulkAction::make('bulk_failed')
                    ->label('Marquer échec')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn($records) => $records->each->update(['status' => 'failed'])),
                Tables\Actions\BulkAction::make('bulk_refunded')
                    ->label('Rembourser')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn($records) => $records->each->update(['status' => 'refunded'])),
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ])
            // Eager load pour éviter N+1 et l’erreur "[$query] unresolvable" (on n’utilise PAS ->query())
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('veteran');
                return $query; // ✅ important si tu n’utilises pas une arrow-fn
            });
        // ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVeteranPayments::route('/'),
            'create' => Pages\CreateVeteranPayment::route('/create'),
            'edit'   => Pages\EditVeteranPayment::route('/{record}/edit'),
        ];
    }
}
