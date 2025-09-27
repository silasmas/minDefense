<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('veteran_asset_logs', function (Blueprint $table) {
            $table->id();

            // Journal rattaché à un bien
            $table->foreignId('asset_id')->constrained('veteran_assets')->cascadeOnDelete();

            // Type d’événement de gestion
            $table->enum('event_type', [
                'created',        // création / enregistrement
                'updated',        // modification de données
                'maintenance',    // entretien / réparation
                'inspection',     // contrôle / visite
                'transfer',       // transfert / réaffectation
                'status_change',  // changement de statut (active -> disposed…)
                'note',           // note libre
            ])->index();

            // Infos libres
            $table->text('notes')->nullable();

            // Montant éventuellement associé (ex: coût maintenance)
            $table->decimal('cost', 14, 2)->nullable();
            $table->char('currency', 3)->nullable();

            // Où / quand l’événement a eu lieu
            $table->dateTime('occurred_at')->nullable()->index();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veteran_asset_logs');
    }
};
