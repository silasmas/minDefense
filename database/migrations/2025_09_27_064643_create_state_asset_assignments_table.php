<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('state_asset_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('state_assets')->cascadeOnDelete();

            // À qui on affecte ? On garde 'veteran' / 'service' pour flexibilité.
            $table->enum('assignee_type', ['veteran','service'])->index();

            // Si veteran: veteran_id rempli. Si service: on peut remplir service_name.
            $table->foreignId('veteran_id')->nullable()->constrained('veterans')->nullOnDelete();
            $table->string('service_name', 150)->nullable();

            // Période d’affectation
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('returned_at')->nullable();

            // Commentaire / statut
            $table->enum('status', ['ongoing','returned','lost'])->default('ongoing')->index();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Exemple: on évite deux affectations ouvertes simultanément
            $table->index(['asset_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_asset_assignments');
    }
};
