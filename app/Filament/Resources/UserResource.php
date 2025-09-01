<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $modelLabel      = 'Utilisateur';
    protected static ?string $pluralModelLabel= 'Utilisateurs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Profil')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nom complet')->required()->maxLength(150),
                Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->tel()->maxLength(30),
                Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
                Forms\Components\Toggle::make('mark_verified')
                    ->label("Marquer l'email comme vérifié")
                    ->dehydrated(false)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('email_verified_at', $state ? now() : null)),
                Forms\Components\DateTimePicker::make('email_verified_at')->label("Email vérifié le")->native(false)->seconds(false)->hiddenOn('create'),
            ]),
            Forms\Components\Section::make('Sécurité')->columns(2)->schema([
                Forms\Components\TextInput::make('password')
                    ->label('Mot de passe')->password()->revealable()
                    ->rule(PasswordRule::defaults())
                    ->required('create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Confirmer')->password()->revealable()->same('password')->dehydrated(false),
            ]),
            Forms\Components\Section::make('Rôles & permissions')->columns(2)->schema([
                Forms\Components\Select::make('roles')->label('Rôles')->relationship('roles','name')->multiple()->preload()->searchable(),
                Forms\Components\Select::make('permissions')->label('Permissions directes')
                    ->relationship('permissions','name')->multiple()->preload()->searchable()
                    ->helperText('Préfère les rôles (Shield).'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TagsColumn::make('roles.name')->label('Rôles')->limit(3),
                Tables\Columns\ToggleColumn::make('is_active')->label('Actif')->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')->label('Vérifié le')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Actif')->trueLabel('Actifs')->falseLabel('Inactifs')->queries(
                    true:  fn ($q) => $q->where('is_active', true),
                    false: fn ($q) => $q->where('is_active', false),
                    blank: fn ($q) => $q,
                ),
                Tables\Filters\TernaryFilter::make('verified')->label('Email vérifié')->trueLabel('Vérifié')->falseLabel('Non vérifié')->queries(
                    true:  fn ($q) => $q->whereNotNull('email_verified_at'),
                    false: fn ($q) => $q->whereNull('email_verified_at'),
                    blank: fn ($q) => $q,
                ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // 👉 Action "Envoyer lien de réinitialisation"
                Tables\Actions\Action::make('send_reset')
                    ->label('Envoyer lien de réinitialisation')
                    ->icon('heroicon-m-key')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        if (! $record->email) {
                            Notification::make()->title("L'utilisateur n'a pas d'email")->danger()->send();
                            return;
                        }
                        $status = Password::sendResetLink(['email' => $record->email]);
                        Notification::make()
                            ->title($status === Password::RESET_LINK_SENT ? 'Lien envoyé' : 'Échec envoi du lien')
                            ->{$status === Password::RESET_LINK_SENT ? 'success' : 'danger'}()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                // 👉 Bulk : envoyer les liens à une sélection
                Tables\Actions\BulkAction::make('bulk_send_reset')
                    ->label('Envoyer liens de réinitialisation')
                    ->icon('heroicon-m-envelope-open')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $ok = 0; $ko = 0;
                        foreach ($records as $u) {
                            if (! $u->email) { $ko++; continue; }
                            $status = Password::sendResetLink(['email' => $u->email]);
                            $status === Password::RESET_LINK_SENT ? $ok++ : $ko++;
                        }
                        Notification::make()
                            ->title("Liens envoyés : {$ok} • Échecs : {$ko}")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name','email','phone'];
    }

    public static function getPages(): array
    {
        return [
            'index'  => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'view'   => UserResource\Pages\ViewUser::route('/{record}'),
            'edit'   => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
