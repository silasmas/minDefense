<?php

namespace App\Filament\Imports;

use App\Models\Veteran;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VeteranImporter extends Importer
{
    protected static ?string $model = Veteran::class;

    public static function getColumns(): array
    {
        // Helpers (peuvent garder n’importe quel nom interne)
        $parseDate = function ($value): ?Carbon {
            if ($value === null || $value === '') return null;
            try { return Carbon::parse($value); } catch (\Throwable) { return null; }
        };

        $normalizePhone = function (?string $value): ?string {
            if (!$value) return null;
            $value = preg_replace('/\D+/', '', $value);
            if (Str::startsWith($value, '0')) $value = '243' . substr($value, 1);
            if (! Str::startsWith($value, '243')) $value = '243' . $value;
            return '+' . $value;
        };

        $mapStatus = function (?string $value): string {
            $value = Str::lower(trim((string) $value));
            return match ($value) {
                'reconnu','reconnue','recognized' => 'recognized',
                'brouillon','draft'                => 'draft',
                'suspendu','suspendue','suspended' => 'suspended',
                'decede','décédé','decedee','décédée','deceased' => 'deceased',
                default => 'recognized',
            };
        };

        $mapCard = function (?string $value): string {
            $value = Str::lower(trim((string) $value));
            return match ($value) {
                'actif','active'                          => 'active',
                'revoque','révoqué','revoquee','révoquée','revoked' => 'revoked',
                'perdu','perdue','lost'                  => 'lost',
                default => 'active',
            };
        };

        $mapGender = function (?string $value): ?string {
            if (!$value) return null;
            $value = Str::upper(trim($value));
            return in_array($value, ['M', 'F'], true) ? $value : null;
        };

        return [
            ImportColumn::make('service_number')->label('Matricule')
                ->requiredMapping()->rules(['required','string','max:64'])->example('VET-25-1CPD0B'),

            ImportColumn::make('nin')->label('NIN')
                ->rules(['nullable','string','max:64'])->example('NIN00342418'),

            ImportColumn::make('firstname')->label('Prénom')
                ->requiredMapping()->rules(['required','string','max:255'])->example('Adam'),

            ImportColumn::make('lastname')->label('Nom')
                ->requiredMapping()->rules(['required','string','max:255'])->example('Marcel'),

            // ImportColumn::make('middlename')->label('Post-nom')
            //     ->rules(['nullable','string','max:255'])->example('Kabasele'),

            ImportColumn::make('gender')->label('Sexe (M/F)')
                ->rules(['nullable', Rule::in(['M','F'])])
                ->castStateUsing(fn ($state) => $mapGender($state))
                ->example('M'),

            ImportColumn::make('birthdate')->label('Date de naissance')
                ->rules(['nullable','date'])
                ->castStateUsing(fn ($state) => $parseDate($state))
                ->example('1966-12-31'),

            // ImportColumn::make('birthplace')->label('Lieu de naissance')
            //     ->rules(['nullable','string','max:255'])->example('Kinshasa'),

            ImportColumn::make('phone')->label('Téléphone (+243...)')
                ->rules(['nullable','string','max:30'])
                ->castStateUsing(fn ($state) => $normalizePhone($state))
                ->example('0812345678'),

            ImportColumn::make('email')->label('E-mail')
                ->rules(['nullable','email','max:255'])->example('adam@example.com'),

            ImportColumn::make('address')->label('Adresse')
                ->rules(['nullable','string','max:255'])->example('18, ave Rémy Raynaud'),

            ImportColumn::make('branch')->label('Branche')
                ->rules(['nullable','string','max:50'])->example('Mer'),

            ImportColumn::make('rank')->label('Grade')
                ->rules(['nullable','string','max:50'])->example('Lieutenant'),

            ImportColumn::make('status')->label('Statut (brouillon/reconnu/suspendu/décédé)')
                ->rules(['nullable', Rule::in(['draft','recognized','suspended','deceased'])])
                ->castStateUsing(fn ($state) => $mapStatus($state))
                ->example('reconnu'),

            ImportColumn::make('card_number')->label('N° carte')
                ->rules(['nullable','string','max:32'])->example('123'),

            ImportColumn::make('card_status')->label('Statut carte (active/révoquée/perdue)')
                ->rules(['nullable', Rule::in(['active','revoked','lost'])])
                ->castStateUsing(fn ($state) => $mapCard($state))
                ->example('active'),

            // ImportColumn::make('card_issued_at')->label('Date de délivrance')
            //     ->rules(['nullable','date'])
            //     ->castStateUsing(fn ($state) => $parseDate($state))
            //     ->example('2024-09-01'),

            ImportColumn::make('card_expires_at')->label('Date d’expiration')
                ->rules(['nullable','date'])
                ->castStateUsing(fn ($state) => $parseDate($state))
                ->example('2026-09-07'),

            ImportColumn::make('photo_path')->label('Chemin photo (optionnel)')
                ->rules(['nullable','string','max:255'])->example('veterans/69.jpg'),

            ImportColumn::make('photo_disk')->label('Disque photo (public/local)')
                ->rules(['nullable','string','max:30'])
                ->castStateUsing(fn ($state) => $state ?: 'public')
                ->example('public'),

            ImportColumn::make('notes')->label('Notes')
                ->rules(['nullable','string'])->example('Ancien combattant…'),
        ];
    }

    public function resolveRecord(): ?Veteran
    {
        $sn  = trim((string) ($this->data['service_number'] ?? ''));
        $nin = trim((string) ($this->data['nin'] ?? ''));

        if ($sn !== '') return Veteran::firstOrNew(['service_number' => $sn]);
        if ($nin !== '') return Veteran::firstOrNew(['nin' => $nin]);

        return new Veteran();
    }

    public function getValidationMessages(): array
    {
        return [
            'service_number.required' => 'Le matricule est obligatoire.',
            'firstname.required'      => 'Le prénom est obligatoire.',
            'lastname.required'       => 'Le nom est obligatoire.',
            'email.email'             => 'Le champ e-mail n’est pas valide.',
            'birthdate.date'          => 'La date de naissance est invalide.',
            'card_issued_at.date'     => 'La date de délivrance est invalide.',
            'card_expires_at.date'    => 'La date d’expiration est invalide.',
            'status.in'               => 'Le statut doit être : draft, recognized, suspended ou deceased.',
            'card_status.in'          => 'Le statut de carte doit être : active, revoked ou lost.',
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'service_number'  => 'matricule',
            'firstname'       => 'prénom',
            'lastname'        => 'nom',
            'middlename'      => 'post-nom',
            'gender'          => 'sexe',
            'birthdate'       => 'date de naissance',
            'birthplace'      => 'lieu de naissance',
            'phone'           => 'téléphone',
            'email'           => 'e-mail',
            'address'         => 'adresse',
            'branch'          => 'branche',
            'rank'            => 'grade',
            'status'          => 'statut',
            'card_number'     => 'n° carte',
            'card_status'     => 'statut carte',
            'card_issued_at'  => 'date de délivrance',
            'card_expires_at' => 'date d’expiration',
            'photo_path'      => 'chemin photo',
            'photo_disk'      => 'disque photo',
            'notes'           => 'notes',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import terminé : ' . number_format($import->successful_rows, 0, ',', ' ')
              . ' ligne' . ($import->successful_rows > 1 ? 's' : '') . ' importée' . ($import->successful_rows > 1 ? 's' : '') . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount, 0, ',', ' ')
                  . ' ligne' . ($failedRowsCount > 1 ? 's' : '') . ' en échec.';
        }

        return $body;
    }
}
