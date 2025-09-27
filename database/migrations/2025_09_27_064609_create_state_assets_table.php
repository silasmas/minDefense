<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('state_assets', function (Blueprint $table) {
            $table->id();

            // Typologie simple
            $table->enum('asset_type', ['materiel','immobilier'])->index();

            // Références inventaire / libre
            $table->string('asset_code', 50)->unique(); // code inventaire (ex: ETAT-2025-000123)
            $table->string('category', 64)->nullable()->index();

            // Désignation & détails
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('serial_number', 120)->nullable(); // n° série (pour matériel)

            // Valeur & statut de cycle
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->char('currency', 3)->default('CDF');
            $table->enum('status', ['active','under_maintenance','disposed'])->default('active')->index();
            $table->date('acquired_at')->nullable();
            $table->date('disposed_at')->nullable();

            // Localisation administrative
            $table->string('country_code', 2)->default('CD')->index();
            $table->string('province', 100)->nullable()->index();
            $table->string('city', 120)->nullable()->index();
            $table->string('address', 190)->nullable();

            // Géoloc (pour carte)
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();

            // Gestion / rattachement administratif (non obligatoire)
            $table->string('managing_agency', 150)->nullable(); // ex: "Min. Défense - Direction Logistique"

            // Médias
            $table->json('photos')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_type','status','province']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_assets');
    }
};
