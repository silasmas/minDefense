<?php
namespace App\Filament\Resources\VeteranResource\Pages;

use App\Filament\Resources\VeteranResource;
use App\Jobs\SendPaymentNotification;
use App\Models\Veteran;
use App\Models\VeteranPayment;
use App\Models\VeteranVerification;
use App\Services\SmsSender;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\HasRelationManagers;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewVeteran extends ViewRecord
{
    use HasRelationManagers; // ← indispensable pour voir les onglets relations
    protected static string $resource = VeteranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ——— Boutons principaux visibles ———
            Actions\Action::make('preview_card')
                ->label('Prévisualiser')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->url(fn($record) => route('veterans.card.preview', $record))
                ->openUrlInNewTab()
                ->button(),

            Actions\Action::make('send_sms_verify')
                ->label('Envoyer vérif SMS')
                ->icon('heroicon-m-paper-airplane')
                ->color('primary')
                ->modalWidth('md')
                ->form([
                    TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->default(fn(Veteran $r) => $r->phone)
                        ->required(),
                    Select::make('next_status')
                        ->label('Statut cible après confirmation')
                        ->options([
                            'recognized' => 'Reconnu',
                            'suspended'  => 'Suspendu',
                        ])
                        ->native(false)
                        ->placeholder('Ne pas changer'),
                    TextInput::make('expires_minutes')
                        ->label('Expiration (minutes)')
                        ->numeric()
                        ->minValue(5)
                        ->default(60)
                        ->required(),
                ])
                ->action(function (array $data, Veteran $record) {
                    $token = Str::random(48);

                    VeteranVerification::create([
                        'veteran_id'  => $record->id,
                        'phone'       => $data['phone'],
                        'token'       => $token,
                        'purpose'     => 'status_confirm',
                        'next_status' => $data['next_status'] ?? null,
                        'expires_at'  => now()->addMinutes((int) $data['expires_minutes']),
                        'sent_at'     => now(),
                    ]);

                    $url = route('veterans.sms.verify', $token);
                    $msg = "Vérification: {$record->lastname} {$record->firstname}. Ouvrez le lien: {$url}";

                    $ok = app(SmsSender::class)->send($data['phone'], $msg);

                    $note = Notification::make()->title($ok ? 'SMS envoyé' : 'Échec envoi SMS');
                    $ok ? $note->success() : $note->danger();
                    $note->send();
                })
                ->button(),

            Actions\EditAction::make()
                ->label('Modifier')
                ->icon('heroicon-m-pencil-square')
                ->color('warning')
                ->button(),
            Actions\Action::make('schedule_payment')
                ->label('Programmer paiement')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->modalWidth('lg')
                ->form([
                    Select::make('payment_type')
                        ->label('Type')
                        ->options([
                            'pension' => 'Pension',
                            'arrears' => 'Arriérés',
                            'aid'     => 'Aide',
                        ])->required()->native(false),

                    DatePicker::make('period_month')
                        ->label('Mois de référence')
                        ->displayFormat('MMMM yyyy')
                        ->visible(fn(\Filament\Forms\Get $get) => $get('payment_type') === 'pension')
                        ->requiredIf('payment_type', 'pension')
                        ->dehydrateStateUsing(fn(?string $state) => $state ? \Carbon\Carbon::parse($state)->startOfMonth() : null),

                    DatePicker::make('period_start')
                        ->label('Période : début')
                        ->visible(fn(\Filament\Forms\Get $get) => $get('payment_type') !== 'pension')
                        ->dehydrateStateUsing(fn(?string $state) => $state ? \Carbon\Carbon::parse($state) : null),

                    DatePicker::make('period_end')
                        ->label('Période : fin')
                        ->visible(fn(\Filament\Forms\Get $get) => $get('payment_type') !== 'pension')
                        ->after('period_start')
                        ->dehydrateStateUsing(fn(?string $state) => $state ? \Carbon\Carbon::parse($state) : null),

                    TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()->minValue(1)->required(),

                    Select::make('currency')
                        ->label('Devise')
                        ->options(['CDF' => 'CDF', 'USD' => 'USD'])
                        ->default('CDF')->required()->native(false),

                    DateTimePicker::make('scheduled_at')
                        ->label('Date/heure prévue de paiement')
                        ->helperText('Utilisée comme date prévue (stockée dans paid_at tant que le statut est “scheduled”).')
                        ->default(now()->addDay()),


                    Textarea::make('notes')
                        ->label('Notes')->rows(2),

                    Toggle::make('send_sms_now')
                        ->label('Envoyer le SMS maintenant')
                        ->default(true),

                    Toggle::make('send_sms_at_schedule')
                        ->label('Programmer l’envoi du SMS à la date prévue')
                        ->default(false),

                    Textarea::make('template')
                        ->label('Modèle SMS')
                        ->rows(3)
                        ->default('Bonjour {prenom} {nom}, votre paiement {type} de {mois} d’un montant de {montant} {devise} est programmé le {date}. Réf: {ref}.'),
                ])
                ->action(function (array $data, Veteran $record) {
                    $periodMonth = null;
                    $periodStart = null;
                    $periodEnd   = null;
$reference = $data['reference'] ?? null;
                    if ($data['payment_type'] === 'pension') {
                        $periodMonth = Carbon::parse($data['period_month'])->startOfMonth();
                    } else {
                        $periodStart = $data['period_start'] ? Carbon::parse($data['period_start']) : null;
                        $periodEnd   = $data['period_end'] ? Carbon::parse($data['period_end']) : null;
                    }


                    if (! $reference) {
                        // Génère une référence unique, ex: REF-69-2509-AB12Z
                        do {
                            $reference = sprintf('REF-%d-%s-%s',
                                $record->id,
                                now()->format('ym'),
                                \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5))
                            );
                        } while (\App\Models\VeteranPayment::where('reference', $reference)->exists());
                    }

// Puis dans $attrs :
                    $attrs = [
                        'veteran_id'   => $record->id,
                        'case_id'      => null,
                        'payment_type' => $data['payment_type'],
                        'period_month' => $periodMonth,
                        'period_start' => $periodStart,
                        'period_end'   => $periodEnd,
                        'amount'       => (float) $data['amount'],
                        'currency'     => $data['currency'],
                        'status'       => 'scheduled',
                        'paid_at'      => $data['scheduled_at'] ? \Carbon\Carbon::parse($data['scheduled_at']) : null, // date prévue
                        'reference'    => $reference,                                                                  // <= ici
                        'notes'        => $data['notes'] ?? null,
                    ];
                    DB::transaction(function () use ($record, $data, $attrs, $periodMonth) {
                        $payment = VeteranPayment::updateOrCreate(
                            [
                                'veteran_id'   => $record->id,
                                'payment_type' => $data['payment_type'],
                                'period_month' => $periodMonth, // null pour arrears/aid → pas de contrainte unique
                            ],
                            $attrs
                        );

                        // SMS
                        $typeFr = ['pension' => 'pension', 'arrears' => 'arriérés', 'aid' => 'aide'][$data['payment_type']] ?? $data['payment_type'];
                        $mois   = $periodMonth ? $periodMonth->isoFormat('MMMM YYYY')
                        : (($attrs['period_start']?->format('d/m/Y') ?? '—') . ' - ' . ($attrs['period_end']?->format('d/m/Y') ?? '—'));

                        $msg = \Illuminate\Support\Str::of((string) $data['template'])
                            ->replace('{prenom}', $record->firstname ?? '')
                            ->replace('{nom}', $record->lastname ?? '')
                            ->replace('{type}', $typeFr)
                            ->replace('{mois}', $mois)
                            ->replace('{montant}', number_format((float) $data['amount'], 0, ' ', ' '))
                            ->replace('{devise}', $data['currency'])
                            ->replace('{date}', optional($attrs['paid_at'])->format('d/m/Y H:i') ?: '—')
                            ->replace('{ref}',  $payment->reference ?: '—'); // <<— référence visible ici

                        if ($data['send_sms_now'] && $record->phone) {
                            app(SmsSender::class)->send($record->phone, (string) $msg);
                        }

                        if ($data['send_sms_at_schedule'] && $attrs['paid_at'] && $record->phone) {
                            SendPaymentNotification::dispatch($record->id, (string) $msg)->delay($attrs['paid_at']);
                        }
                    });

                    Notification::make()->title('Paiement programmé')->success()->send();
                }),
            // ——— Groupe : Identifiants (se replie en dropdown si déborde) ———
            Actions\ActionGroup::make([
                Actions\Action::make('assign_card')
                    ->label('Assigner n° de carte')
                    ->icon('heroicon-m-credit-card')
                    ->requiresConfirmation()
                    ->action(function (Veteran $record) {
                        if (! $record->card_number) {
                            do {
                                $num = 'CARD-' . now()->format('y') . '-' . Str::upper(Str::random(6));
                            } while (Veteran::where('card_number', $num)->exists());
                            $record->card_number = $num;
                        }
                        if (! $record->card_expires_at) {
                            $record->card_expires_at = now()->addYear();
                        }
                        $record->card_status = $record->card_status ?? 'active';
                        $record->save();

                        Notification::make()->title('Carte mise à jour')->success()->send();
                    }),

                Actions\Action::make('assign_service_number')
                    ->label('Assigner matricule')
                    ->icon('heroicon-m-hashtag')
                    ->requiresConfirmation()
                    ->action(function (Veteran $record) {
                        if (! $record->service_number) {
                            do {
                                $sn = 'VET-' . now()->format('y') . '-' . Str::upper(Str::random(8));
                            } while (Veteran::where('service_number', $sn)->exists());
                            $record->service_number = $sn;
                        }
                        $record->save();

                        Notification::make()->title('Matricule mis à jour')->success()->send();
                    }),

                Actions\Action::make('assign_nin')
                    ->label('Assigner N° national')
                    ->icon('heroicon-m-identification')
                    ->requiresConfirmation()
                    ->action(function (Veteran $record) {
                        if (! $record->nin) {
                            do {
                                $nin = 'NIN-' . now()->format('y') . '-' . Str::upper(Str::random(8));
                            } while (Veteran::where('nin', $nin)->exists());
                            $record->nin = $nin;
                        }
                        $record->save();

                        Notification::make()->title('NIN mis à jour')->success()->send();
                    }),
            ])
                ->label('Identifiants')
                ->icon('heroicon-m-identification')
                ->color('gray'),

            // ——— Groupe : Statut carte ———
            Actions\ActionGroup::make([
                Actions\Action::make('revoke_card')
                    ->label('Révoquer la carte')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->visible(fn(Veteran $r) => $r->card_status !== 'revoked')
                    ->form([
                        Textarea::make('reason')->label('Motif')->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data, Veteran $record) {
                        $record->card_status        = 'revoked';
                        $record->card_revoked_at    = now();
                        $record->card_status_reason = $data['reason'];
                        $record->save();

                        Notification::make()->title('Carte révoquée')->danger()->send();
                    }),

                Actions\Action::make('reactivate_card')
                    ->label('Réactiver la carte')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->visible(fn(Veteran $r) => $r->card_status === 'revoked')
                    ->requiresConfirmation()
                    ->action(function (Veteran $record) {
                        $record->card_status     = 'active';
                        $record->card_revoked_at = null;
                        $record->save();

                        Notification::make()->title('Carte réactivée')->success()->send();
                    }),
            ])
                ->label('Statut carte')
                ->icon('heroicon-m-shield-exclamation')
                ->color('gray'),
        ];
    }
}
