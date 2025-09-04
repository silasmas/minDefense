<?php
namespace App\Filament\Resources\VeteranResource\Pages;

use Filament\Actions;
use App\Models\Veteran;
use App\Services\SmsSender;
use Illuminate\Support\Str;
use App\Models\VeteranVerification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\VeteranResource;
use Filament\Resources\Pages\Concerns\HasRelationManagers;

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
            ->url(fn ($record) => route('veterans.card.preview', $record))
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
                    ->default(fn (Veteran $r) => $r->phone)
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
                ->visible(fn (Veteran $r) => $r->card_status !== 'revoked')
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
                ->visible(fn (Veteran $r) => $r->card_status === 'revoked')
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
