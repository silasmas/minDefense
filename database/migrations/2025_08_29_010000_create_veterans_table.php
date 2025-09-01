<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Exécute la migration. */
    public function up(): void
    {
        // Table maîtresse des anciens combattants (identité minimale + infos de service)
        Schema::create('veterans', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identité de base (recherche)
            $table->string('service_number', 64)->nullable()->unique()->comment('Matricule / numéro de service');
            $table->string('nin', 64)->nullable()->unique()->comment('Numéro d’Identité National (si applicable)');
            $table->string('firstname');
            $table->string('lastname');
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();

            // Coordonnées simples (MVP)
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable()->comment('Adresse libre (ligne unique pour MVP)');

            // Données de service
            $table->string('branch', 50)->nullable()->comment('Armée de terre / air / mer, etc.');
            $table->string('rank', 50)->nullable()->comment('Grade');
            $table->date('service_start_date')->nullable();
            $table->date('service_end_date')->nullable();

            $table->enum('status', ['draft','recognized','suspended','deceased'])->default('draft');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index utiles pour la recherche
            $table->index(['lastname','firstname']);
            $table->index(['status']);
        });
    }

    /** Annule la migration. */
    public function down(): void
    {
        Schema::dropIfExists('veterans');
    }
};
