<?php
namespace App\Filament\Resources\VeteranResource\Pages;

use App\Filament\Resources\VeteranResource;
use App\Models\Veteran;
use App\Models\VeteranVerification;
use App\Services\SmsSender;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewVeteran extends ViewRecord
{
    protected static string $resource = VeteranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewCcard')
                ->label('Prévisualiser')
                ->icon('heroicon-m-eye')
                ->url(fn($record) => route('veterans.card.preview', $record))
                ->openUrlInNewTab(),

            Actions\Action::make('send_sms_verify')
                ->label('Envoyer vérif SMS')
                ->icon('heroicon-m-paper-airplane')
                ->form([
                    TextInput::make('phone')
                        ->label('Téléphone')
                        ->default(fn(\App\Models\Veteran $r) => $r->phone)
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
                        ->numeric()->default(60)->required(),
                ])
                ->action(function (array $data, \App\Models\Veteran $record) {
                    $token = Str::random(48);

                    $verify = VeteranVerification::create([
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

                    Notification::make()
                        ->title($ok ? 'SMS envoyé' : 'Échec envoi SMS')
                        ->{$ok ? 'success' : 'danger'}()
                        ->send();
                }),
            Actions\Action::make('revoke_card')
                ->label('Révoquer la carte')
                ->icon('heroicon-m-no-symbol')
                ->color('danger')
                ->visible(fn(Veteran $r) => $r->card_status !== 'revoked')
                ->form([
                    Textarea::make('reason')->label('Motif')->required(),
                ])
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
                ->visible(fn(Veteran $r) => $r->card_status === 'revoked')
                ->requiresConfirmation()
                ->action(function (Veteran $record) {
                    $record->card_status     = 'active';
                    $record->card_revoked_at = null;
                    $record->save();

                    Notification::make()->title('Carte réactivée')->success()->send();
                }),
            Actions\Action::make('assign_card')
                ->label('Assigner n° de carte')
                ->icon('heroicon-m-credit-card')
                ->requiresConfirmation()
                ->action(function (Veteran $record) {
                    if (! $record->card_number) {
                        $record->card_number = 'VET-' . now()->format('y') . '-' . Str::upper(Str::random(8));
                    }
                    if (! $record->card_expires_at) {
                        $record->card_expires_at = now()->addYear();
                    }
                    // si statut carte existe (voir amélioration #1 ci-dessous)
                    if (property_exists($record, 'card_status') || array_key_exists('card_status', $record->getAttributes())) {
                        $record->card_status = $record->card_status ?? 'active';
                    }
                    $record->save();

                    Notification::make()
                        ->title('Carte mise à jour')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('carte_pdf')
                ->label('Carte PDF')
                ->icon('heroicon-m-identification')
                ->url(fn(Veteran $record) => route('veterans.card', $record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
