<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('veteran_assets', function (Blueprint $table) {
            $table->id();

            // FK vers le vétéran propriétaire du bien
            $table->foreignId('veteran_id')->constrained('veterans')->cascadeOnDelete();

            // Typologie simple : matériel (véhicules, équipements), immobilier (parcelles, maisons…)
            $table->enum('asset_type', ['materiel','immobilier'])->index();

            // Catégorie libre (ex: "véhicule", "terrain", "bâtiment", "équipement informatique", …)
            $table->string('category', 64)->nullable()->index();

            // Désignation courte du bien
            $table->string('title', 150);

            // Détail / description
            $table->text('description')->nullable();

            // Valeur estimée + devise
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->char('currency', 3)->default('CDF');

            // Statut de gestion du bien
            $table->enum('status', ['active','under_maintenance','disposed'])->default('active')->index();

            // Dates de cycle de vie (acquisition / cession)
            $table->date('acquired_at')->nullable();
            $table->date('disposed_at')->nullable();

            // Localisation administrative (pour filtres rapides)
            $table->string('country_code', 2)->default('CD')->index();
            $table->string('province', 100)->nullable()->index();
            $table->string('city', 120)->nullable()->index();
            $table->string('address', 190)->nullable();

            // Géolocalisation
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();

            // Médias (chemins des photos) optionnels
            $table->json('photos')->nullable(); // ex: ["assets/1.jpg","assets/2.jpg"]

            // Trace & soft delete
            $table->timestamps();
            $table->softDeletes();

            // Exemple d’index composé utile pour la carte
            $table->index(['asset_type','status','province']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veteran_assets');
    }
};
