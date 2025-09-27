<?php


// app/Filament/Resources/VeteranPaymentResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\VeteranPaymentResource\Pages;
use App\Models\VeteranPayment;
use App\Models\Veteran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
    protected static ?int    $navigationSort  = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // == LIEN AU VÉTÉRAN ==
            Forms\Components\Select::make('veteran_id')
                ->label('Vétéran')                          // FK -> veterans.id
                ->relationship(name: 'veteran', titleAttribute: 'lastname') // base pour la recherche
                ->getOptionLabelFromRecordUsing(function (Veteran $v) {
                    // Affiche "Nom Prénom (Matricule)" si dispo
                    $mat = $v->service_number ? " ({$v->service_number})" : '';
                    return trim(($v->full_name ?? ($v->lastname.' '.$v->firstname)).$mat);
                })
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Sélectionnez le vétéran bénéficiaire du paiement.'),

            // == (OPTIONNEL) LIEN À UN DOSSIER/CASE ==
            Forms\Components\Select::make('case_id')
                ->label('Dossier (optionnel)')              // FK -> veteran_cases.id
                ->relationship('case', 'id')                // on affiche l’ID si tu n’as pas de champ "title"
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Associez ce paiement à un dossier si nécessaire.'),

            // == TYPE DE PAIEMENT ==
            Forms\Components\Select::make('payment_type')
                ->label('Type de paiement')                 // enum: pension | arrears | aid
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
                ->label('Mois de référence')                // date (ex: 2025-08-01)
                ->native(false)                             // meilleur UX (calendrier web)
                ->closeOnDateSelection()
                ->afterStateUpdated(function (Set $set, $state) {
                    // Force au 1er jour du mois, pour respecter ton design "YYYY-MM-01"
                    if ($state) {
                        $set('period_month', Carbon::parse($state)->startOfMonth()->toDateString());
                    }
                })
                ->helperText('Pour les pensions mensuelles: un jour au choix, il sera enregistré au 1er du mois.'),

            Forms\Components\Grid::make()
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('period_start')
                        ->label('Début de période')         // date
                        ->helperText('Optionnel — utile pour les aides/arriérés couvrant un intervalle.'),
                    Forms\Components\DatePicker::make('period_end')
                        ->label('Fin de période')           // date
                        ->helperText('Optionnel — fin de l’intervalle couvert.'),
                ]),

            // == MONTANT & DEVISE ==
            Forms\Components\TextInput::make('amount')
                ->label('Montant')                          // decimal(12,2)
                ->numeric()
                ->required()
                ->helperText('Exemple: 150.00'),

            Forms\Components\Select::make('currency')
                ->label('Devise')                           // char(3) : USD par défaut
                ->options([
                    'USD' => 'USD',
                    'CDF' => 'CDF',
                    'EUR' => 'EUR',
                ])
                ->default('USD')
                ->required()
                ->helperText('Code devise sur 3 caractères (ISO).'),

            // == STATUT & DATE DE PAIEMENT ==
            Forms\Components\Select::make('status')
                ->label('Statut')                           // enum: scheduled | paid | failed | refunded
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
                ->label('Payé le')                          // datetime
                ->seconds(false)
                ->helperText('Date/heure de paiement effectif (utile si statut = Payé).'),

            // == RÉFÉRENCE & NOTES ==
            Forms\Components\TextInput::make('reference')
                ->label('Référence')                        // index, 128
                ->maxLength(128)
                ->helperText('Référence bancaire / Mobile Money / pièce comptable.'),
            Forms\Components\Textarea::make('notes')
                ->label('Notes')                            // texte libre
                ->rows(3)
                ->helperText('Informations complémentaires.'),

            // == CONTRAINTE D’UNICITÉ MÉTIER (même vétéran + type + mois) ==
            Forms\Components\Section::make('Validation')
                ->schema([])
                ->hidden() // section invisible, juste pour regrouper la règle ci-dessous
                ->afterValidation(function (Forms\Components\Component $component, Forms\Form $form) {
                    // Rien ici : on met la règle sur period_month ci-dessous
                }),
        ])
        ->columns(2)
        // Règle d'unicité appliquée sur period_month, dépendant de veteran_id & payment_type
        ->rules([
            'period_month' => [
                function (Get $get) {
                    return Rule::unique('veteran_payments', 'period_month')
                        ->where('veteran_id', $get('veteran_id'))
                        ->where('payment_type', $get('payment_type'))
                        ->ignore(request()->route('record')); // ignore lors de l’édition
                },
            ],
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Vétéran: nom complet + matricule (depuis la relation)
                TextColumn::make('veteran.id')
                    ->label('Vétéran')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->veteran) return '—';
                        $n = $record->veteran->full_name ?: trim(($record->veteran->lastname ?? '').' '.($record->veteran->firstname ?? ''));
                        $m = $record->veteran->service_number ? " ({$record->veteran->service_number})" : '';
                        return trim($n.$m);
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
                    ->formatStateUsing(fn (string $state) => [
                        'pension' => 'Pension',
                        'arrears' => 'Arriéré',
                        'aid'     => 'Aide',
                    ][$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
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
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2, ',', ' ').' '.($record->currency ?? ''))
                    ->sortable(),

                // Statut (badge)
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => [
                        'scheduled' => 'Planifié',
                        'paid'      => 'Payé',
                        'failed'    => 'Échec',
                        'refunded'  => 'Remboursé',
                    ][$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
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
                    ->getStateUsing(fn ($record) => filled($record->notes))
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
                            ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '>=', $v))
                            ->when($data['to'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Marquer comme "Payé" (raccourci d’affectation)
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marquer payé')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'paid')
                    ->action(function (VeteranPayment $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => $record->paid_at ?? now(),
                        ]);
                    }),

                // Marquer "Échec"
                Tables\Actions\Action::make('mark_failed')
                    ->label('Marquer échec')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'failed')
                    ->action(fn (VeteranPayment $record) => $record->update(['status' => 'failed'])),

                // Marquer "Remboursé"
                Tables\Actions\Action::make('mark_refunded')
                    ->label('Rembourser')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'refunded')
                    ->action(fn (VeteranPayment $record) => $record->update(['status' => 'refunded'])),
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
                    ->action(fn ($records) => $records->each->update(['status' => 'failed'])),
                Tables\Actions\BulkAction::make('bulk_refunded')
                    ->label('Rembourser')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update(['status' => 'refunded'])),
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
