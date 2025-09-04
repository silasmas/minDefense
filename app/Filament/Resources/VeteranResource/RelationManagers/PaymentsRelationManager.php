<?php

namespace App\Filament\Resources\VeteranResource\RelationManagers;

use App\Models\VeteranPayment;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Paiements';
    protected static ?string $icon  = 'heroicon-m-banknotes';

    public function table(Table $table): Table
    {
        return $table
            // 🔹 on ne veut pas de clic ligne → ouvre un enregistrement
            ->recordUrl(null)
            ->recordAction(null)

            // 🔹 place les actions AVANT les colonnes, donc visibles en boutons
            ->actionsPosition(ActionsPosition::BeforeCells)

            // 🔹 par défaut, on montre les “programmés”
            ->modifyQueryUsing(fn ($q) => $q->where('status', 'scheduled')->orderBy('paid_at'))

            // 🔹 entête : bouton pour programmer rapidement un paiement
            ->headerActions([
                Tables\Actions\Action::make('schedule')
                    ->label('Programmer paiement')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('payment_type')
                            ->label('Type')
                            ->options(['pension'=>'Pension','arrears'=>'Arriérés','aid'=>'Aide'])
                            ->required()->native(false),

                        Forms\Components\DatePicker::make('period_month')
                            ->label('Mois de référence')
                            ->displayFormat('MMMM yyyy')
                            ->visible(fn (Get $get) => $get('payment_type') === 'pension')
                            ->requiredIf('payment_type','pension')
                            ->dehydrateStateUsing(fn (?string $state) => $state ? Carbon::parse($state)->startOfMonth() : null),

                        Forms\Components\DatePicker::make('period_start')
                            ->label('Période : début')
                            ->visible(fn (Get $get) => $get('payment_type') !== 'pension')
                            ->dehydrateStateUsing(fn (?string $state) => $state ? Carbon::parse($state) : null),

                        Forms\Components\DatePicker::make('period_end')
                            ->label('Période : fin')
                            ->visible(fn (Get $get) => $get('payment_type') !== 'pension')
                            ->after('period_start')
                            ->dehydrateStateUsing(fn (?string $state) => $state ? Carbon::parse($state) : null),

                        Forms\Components\TextInput::make('amount')->label('Montant')->numeric()->minValue(1)->required(),
                        Forms\Components\Select::make('currency')->label('Devise')->options(['CDF'=>'CDF','USD'=>'USD'])->default('CDF')->required()->native(false),
                        Forms\Components\DateTimePicker::make('scheduled_at')->label('Date/heure prévue')->default(now()->addHour()),
                        Forms\Components\Textarea::make('notes')->label('Notes')->rows(2),
                        Forms\Components\Toggle::make('send_sms_now')->label('Envoyer SMS maintenant')->default(true),
                    ])
                    ->action(function (array $data) {
                        $veteran = $this->getOwnerRecord(); // 👈 le vétéran parent
                        $periodMonth = $data['payment_type']==='pension'
                            ? Carbon::parse($data['period_month'])->startOfMonth()
                            : null;

                        // générer une référence unique
                        do {
                            $ref = sprintf('REF-%d-%s-%s', $veteran->id, now()->format('ym'), Str::upper(Str::random(5)));
                        } while (VeteranPayment::where('reference', $ref)->exists());

                        $p = $veteran->payments()->updateOrCreate(
                            [
                                'payment_type' => $data['payment_type'],
                                'period_month' => $periodMonth,
                            ],
                            [
                                'period_start' => $data['period_start'] ?? null,
                                'period_end'   => $data['period_end']   ?? null,
                                'amount'       => (float) $data['amount'],
                                'currency'     => $data['currency'],
                                'status'       => 'scheduled',
                                'paid_at'      => $data['scheduled_at'] ?? null, // “prévu”
                                'reference'    => $ref,
                                'notes'        => $data['notes'] ?? null,
                            ]
                        );

                        if (!empty($data['send_sms_now']) && $veteran->phone) {
                            $type = ['pension'=>'pension','arrears'=>'arriérés','aid'=>'aide'][$p->payment_type] ?? $p->payment_type;
                            $mois = $p->period_month ? $p->period_month->isoFormat('MMMM YYYY')
                                   : (($p->period_start?->format('d/m/Y') ?? '—').' - '.($p->period_end?->format('d/m/Y') ?? '—'));
                            $msg = "Bonjour {$veteran->firstname} {$veteran->lastname}, votre paiement {$type} de {$mois} " .
                                   "d’un montant de ".number_format((float)$p->amount,0,' ',' ')." {$p->currency} est programmé le ".
                                   (optional($p->paid_at)->format('d/m/Y H:i') ?: '—').". Réf: {$p->reference}.";
                            app(\App\Services\SmsSender::class)->send($veteran->phone, $msg);
                        }

                        Notification::make()->title('Paiement programmé')->success()->send();
                    }),
            ])

            ->columns([
                Tables\Columns\BadgeColumn::make('payment_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $s) => ['pension'=>'Pension','arrears'=>'Arriérés','aid'=>'Aide'][$s] ?? $s),

                Tables\Columns\TextColumn::make('period_month')->label('Mois')->date('m/Y')->toggleable(),

                Tables\Columns\TextColumn::make('amount')->label('Montant')->alignRight()
                    ->formatStateUsing(fn ($v, $r) => number_format((float)$v,0,' ',' ').' '.($r->currency ?? 'CDF')),

                Tables\Columns\BadgeColumn::make('status')->label('Statut')
                    ->colors(['warning'=>'scheduled','success'=>'paid','danger'=>'failed','gray'=>'refunded'])
                    ->formatStateUsing(fn ($s) => ['scheduled'=>'Programmé','paid'=>'Payé','failed'=>'Échoué','refunded'=>'Annulé'][$s] ?? $s),

                Tables\Columns\TextColumn::make('paid_at')->label('Prévu / Payé le')->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('reference')->label('Réf.')->copyable()->limit(20),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')
                    ->options(['scheduled'=>'Programmé','paid'=>'Payé','failed'=>'Échoué','refunded'=>'Annulé'])
                    ->default('scheduled'),
                Tables\Filters\Filter::make('due')->label('Échéance passée')
                    ->query(fn ($q) => $q->where('status','scheduled')->whereNotNull('paid_at')->where('paid_at','<=',now())),
                Tables\Filters\Filter::make('upcoming')->label('À venir')
                    ->query(fn ($q) => $q->where('status','scheduled')->where('paid_at','>',now())),
            ])

            ->actions([
                // 🔸 EXÉCUTER (bouton vert)
                Tables\Actions\Action::make('run')
                    ->label('Exécuter')
                    ->button()->color('success')->icon('heroicon-m-play')
                    ->visible(fn ($record) => $record->status === 'scheduled')
                    ->form([
                        Forms\Components\DateTimePicker::make('paid_at')->label('Payé le')->default(now())->required(),
                        Forms\Components\TextInput::make('reference')->label('Référence')->placeholder('Auto')->maxLength(128),
                        Forms\Components\Toggle::make('notify')->label('Notifier par SMS')->default(true),
                        Forms\Components\Textarea::make('message')->label('Modèle SMS')->rows(2)
                            ->default('Bonjour {prenom} {nom}, votre paiement {type} de {mois} de {montant} {devise} a été exécuté. Réf: {ref}.'),
                    ])
                    ->action(function (array $data, VeteranPayment $record) {
                        $record->status  = 'paid';
                        $record->paid_at = $data['paid_at'] ?? now();
                        if (! $record->reference) {
                            do { $ref = sprintf('REF-%d-%s-%s',$record->veteran_id,now()->format('ym'),Str::upper(Str::random(5))); }
                            while (VeteranPayment::where('reference',$ref)->exists());
                            $record->reference = $data['reference'] ?: $ref;
                        } elseif (!empty($data['reference'])) {
                            $record->reference = $data['reference'];
                        }
                        $record->save();

                        if (!empty($data['notify']) && $record->veteran?->phone) {
                            $v = $record->veteran;
                            $type = ['pension'=>'pension','arrears'=>'arriérés','aid'=>'aide'][$record->payment_type] ?? $record->payment_type;
                            $mois = $record->period_month ? $record->period_month->isoFormat('MMMM YYYY')
                                   : (($record->period_start?->format('d/m/Y') ?? '—').' - '.($record->period_end?->format('d/m/Y') ?? '—'));
                            $msg = Str::of($data['message'])
                                ->replace('{prenom}', $v->firstname ?? '')
                                ->replace('{nom}',    $v->lastname ?? '')
                                ->replace('{type}',   $type)
                                ->replace('{mois}',   $mois)
                                ->replace('{montant}', number_format((float)$record->amount,0,' ',' '))
                                ->replace('{devise}',  $record->currency)
                                ->replace('{ref}',     $record->reference);
                            app(\App\Services\SmsSender::class)->send($v->phone, (string)$msg);
                        }

                        Notification::make()->title('Paiement exécuté')->success()->send();
                    }),

                // 🔸 REPLANIFIER (bouton gris)
                Tables\Actions\Action::make('reschedule')
                    ->label('Replanifier')
                    ->button()->color('gray')->icon('heroicon-m-clock')
                    ->visible(fn ($record) => $record->status === 'scheduled')
                    ->form([ Forms\Components\DateTimePicker::make('new_date')->label('Nouvelle date')->required(), ])
                    ->action(function (array $data, VeteranPayment $record) {
                        $record->paid_at = $data['new_date'];
                        $record->save();
                        Notification::make()->title('Paiement replanifié')->success()->send();
                    }),

                // 🔸 ANNULER (bouton rouge)
                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->button()->color('danger')->icon('heroicon-m-x-circle')
                    ->visible(fn ($record) => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->form([ Forms\Components\Textarea::make('reason')->label('Motif')->required()->rows(2), ])
                    ->action(function (array $data, VeteranPayment $record) {
                        $record->status  = 'refunded';
                        $record->paid_at = null;
                        $record->notes   = trim(($record->notes ? $record->notes.PHP_EOL : '').
                            'Annulé le '.now()->format('d/m/Y H:i').' — '.$data['reason']);
                        $record->save();
                        Notification::make()->title('Paiement annulé')->danger()->send();
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_run')
                    ->label('Exécuter')
                    ->icon('heroicon-m-play')->color('success')->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $p) if ($p->status === 'scheduled') { $p->status='paid'; $p->paid_at=now(); $p->save(); }
                        Notification::make()->title('Paiements exécutés')->success()->send();
                    }),

                Tables\Actions\BulkAction::make('bulk_cancel')
                    ->label('Annuler')
                    ->icon('heroicon-m-x-circle')->color('danger')
                    ->form([ Forms\Components\Textarea::make('reason')->label('Motif')->required(), ])
                    ->action(function (Collection $records, array $data) {
                        foreach ($records as $p) if ($p->status === 'scheduled') { $p->status='refunded'; $p->paid_at=null; $p->save(); }
                        Notification::make()->title('Paiements annulés')->danger()->send();
                    }),
            ]);
    }
}
