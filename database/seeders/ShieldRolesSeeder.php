<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ShieldRolesSeeder extends Seeder
{
    /**
     * Crée les rôles (opérateur guichet, instructeur, superviseur, caissier, admin)
     * et assigne un jeu de permissions compatible avec Filament Shield.
     *
     * NB: Assurez-vous que le guard_name utilisé par Filament est bien 'web'.
     */
    public function run(): void
    {
        $guard = 'web';

        // --- Ensemble des permissions de l’application ---
        $permissions = [
            // Dossiers & demandes
            'dossier.view', 'dossier.create', 'dossier.update', 'dossier.delete',
            'application.view', 'application.create', 'application.update', 'application.delete',
            'application.decide', // approuver/rejeter
            'application.request-info',

            // Documents & vérifications
            'document.view', 'document.upload', 'document.validate',

            // Paiements
            'payment.view', 'payment.create-intent', 'payment.capture', 'payment.refund',

            // RDV & file d’attente
            'appointment.view', 'appointment.book', 'appointment.checkin', 'queue.manage',

            // Catalogue & workflow
            'service.view', 'service.manage', 'workflow.manage',

            // Messages & notifications
            'notification.view', 'notification.send', 'inbox.view', 'inbox.reply',

            // Reporting
            'reporting.view',

            // Administration
            'user.manage', 'role.manage', 'config.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        // --- Rôles ---
        $roles = [
            'operateur_guichet' => [
                'dossier.view', 'application.view',
                'appointment.view', 'appointment.checkin',
                'queue.manage',
                'inbox.view', 'notification.view',
            ],
            'instructeur' => [
                'dossier.view', 'application.view', 'application.update', 'application.request-info',
                'document.view', 'document.validate',
                'reporting.view',
                'inbox.view', 'inbox.reply',
            ],
            'superviseur' => [
                'dossier.view', 'application.view', 'application.update', 'application.decide',
                'document.view', 'document.validate',
                'reporting.view',
                'notification.view', 'notification.send',
                'service.view',
            ],
            'caissier' => [
                'payment.view', 'payment.create-intent', 'payment.capture', 'payment.refund',
                'invoice.view',
                'reporting.view',
            ],
            'admin' => $permissions, // accès complet
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions($perms);
        }
    }
}
