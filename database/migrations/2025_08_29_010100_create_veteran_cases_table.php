<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dossiers rattachés à un ancien combattant (statut, pension, carte de soins, etc.)
        Schema::create('veteran_cases', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('veteran_id')->constrained('veterans')->cascadeOnDelete();
            $table->string('case_number', 32)->unique()->comment('Numéro de dossier lisible');

            $table->enum('case_type', ['status','pension','healthcard','aid'])
                  ->default('status')->comment('Type de dossier');

            $table->enum('current_status', ['draft','submitted','under_review','approved','rejected','closed'])
                  ->default('draft');

            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->text('summary')->nullable()->comment('Résumé libre du dossier');
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['veteran_id','case_type','current_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veteran_cases');
    }
};
