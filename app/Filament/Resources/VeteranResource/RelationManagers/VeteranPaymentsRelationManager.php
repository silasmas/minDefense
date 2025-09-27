<?php
namespace App\Filament\Resources\VeteranResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VeteranPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title       = 'Paiements';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('payment_type')->label('Type')->options([
                    'pension' => 'Pension',
                    'arrears' => 'Arriérés',
                    'aid'     => 'Aide',
                ])->required()->native(false),

                Forms\Components\DatePicker::make('period_month')
                    ->label('Mois de référence')
                    ->displayFormat('Y-m')
                    ->native(false),

                Forms\Components\TextInput::make('amount')
                    ->label('Montant')->numeric()->required()
                    ->prefixIcon('heroicon-m-currency-dollar'),

                Forms\Components\TextInput::make('currency')->default('USD')->maxLength(3),

                Forms\Components\Select::make('status')->label('Statut')->options([
                    'scheduled' => 'Planifié',
                    'paid'      => 'Payé',
                    'failed'    => 'Échoué',
                    'refunded'  => 'Remboursé',
                ])->default('paid')->native(false),

                Forms\Components\DateTimePicker::make('paid_at')->label('Payé le'),

                Forms\Components\TextInput::make('reference')
                    ->label('Référence')->maxLength(128),

                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                        Tables\Columns\TextColumn::make('period_month')->label('Mois')->date('m/Y')->sortable(),
                        Tables\Columns\TextColumn::make('amount')->label('Montant')
                            ->formatStateUsing(fn($state, $record) => number_format((float) $state, 0, ' ', ' ') . ' ' . ($record->currency ?? 'CDF'))
                            ->sortable(),
                        Tables\Columns\TextColumn::make('paid_at')->label('Payé le')->dateTime('d/m/Y H:i')->sortable(),
                        Tables\Columns\TextColumn::make('payment_type')->label('Type'),
                        Tables\Columns\BadgeColumn::make('status')->label('Statut')->colors([
                            'success' => 'paid',
                            'warning' => 'pending',
                            'danger'  => 'failed',
                        ]),


                Tables\Columns\TextColumn::make('paid_at')->label('Payé le')->dateTime(),
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->limit(18)->tooltip(fn($record) => $record->reference),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),

                Tables\Actions\Action::make('notify_sms')
                    ->label('Notifier par SMS')
                    ->icon('heroicon-m-bell')
                    ->form([
                        Forms\Components\Select::make('mode')->label('Mode')->options([
                            'resume' => 'Vrac (résumé par vétéran)',
                            'detail' => 'Détaillé (lignes dans 1 SMS)',
                        ])->default('resume')->required()->native(false),
                        Forms\Components\DatePicker::make('period_month')
                            ->label('Mois (filtre optionnel)')
                            ->native(false),
                    ])
                    ->action(function (array $data, \App\Models\Veteran $ownerRecord) {
                        $q = $ownerRecord->payments();
                        if (! empty($data['period_month'])) {
                            $month = \Carbon\Carbon::parse($data['period_month'])->startOfMonth();
                            $q->whereDate('period_month', $month);
                        }
                        $rows = $q->orderBy('period_month', 'asc')->get();

                        if ($rows->isEmpty()) {
                            \Filament\Notifications\Notification::make()->title('Aucun paiement')->warning()->send();
                            return;
                        }

                        $phone = $ownerRecord->phone;
                        if (! $phone) {
                            \Filament\Notifications\Notification::make()->title('Pas de téléphone')->danger()->send();
                            return;
                        }

                        $currency = $rows->first()->currency ?? 'CDF';

                        if ($data['mode'] === 'resume') {
                            $total = (float) $rows->sum('amount');
                            $mois  = $rows->map(fn($record) => \Carbon\Carbon::parse($record->period_month)->format('m/Y'))->unique()->implode(', ');
                            $msg   = "Pension: total " . number_format($total, 0, ' ', ' ') . " {$currency}. Périodes: {$mois}.";
                        } else {
                            // détail en lignes (peut scinder en 2 SMS si très long, Twilio concatène)
                            $lines = $rows->map(function ($record) use ($currency) {
                                $m = \Carbon\Carbon::parse($record->period_month)->format('m/Y');
                                return "{$m}: " . number_format((float) $record->amount, 0, ' ', ' ') . " {$currency}";
                            })->implode('; ');
                            $msg = "Pension détaillée: {$lines}.";
                        }

                        $ok = app(\App\Services\SmsSender::class)->send($phone, $msg);
                        \Filament\Notifications\Notification::make()
                            ->title($ok ? 'SMS envoyé' : 'Échec envoi SMS')
                            ->{$ok ? 'success' : 'danger'}()
                            ->send();
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('bulk_notify_sms')
                    ->label('Notifier (paiements sélectionnés)')
                    ->icon('heroicon-m-bell-alert')
                    ->form([
                        Forms\Components\Select::make('mode')->label('Mode')->options([
                            'resume' => 'Vrac (par vétéran)',
                            'detail' => 'Détaillé (lignes)',
                        ])->default('resume')->required()->native(false),
                    ])
                    ->action(function (\Illuminate\Support\Collection $records, array $data) {
                        // regrouper par vétéran
                        $byVet = $records->groupBy('veteran_id');

                        foreach ($byVet as $veteranId => $rows) {
                            $vet = \App\Models\Veteran::find($veteranId);
                            if (! $vet || ! $vet->phone) {
                                continue;
                            }

                            $currency = $rows->first()->currency ?? 'CDF';

                            if ($data['mode'] === 'resume') {
                                $total = (float) $rows->sum('amount');
                                $mois  = $rows->sortBy('period_month')->map(fn($record) => \Carbon\Carbon::parse($record->period_month)->format('m/Y'))->unique()->implode(', ');
                                $msg   = "Pension: total " . number_format($total, 0, ' ', ' ') . " {$currency}. Périodes: {$mois}.";
                            } else {
                                $lines = $rows->sortBy('period_month')->map(function ($record) use ($currency) {
                                    $m = \Carbon\Carbon::parse($record->period_month)->format('m/Y');
                                    return "{$m}: " . number_format((float) $record->amount, 0, ' ', ' ') . " {$currency}";
                                })->implode('; ');
                                $msg = "Pension détaillée: {$lines}.";
                            }

                            app(\App\Services\SmsSender::class)->send($vet->phone, $msg);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Notifications envoyées')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
