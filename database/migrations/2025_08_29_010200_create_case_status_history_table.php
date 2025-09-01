<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historique des changements de statut d’un dossier (traçabilité)
        Schema::create('case_status_history', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('case_id')->constrained('veteran_cases')->cascadeOnDelete();
            $table->enum('status', ['draft','submitted','under_review','approved','rejected','closed']);

            // Pas de FK ici pour rester indépendant du module "users" (facultatif)
            $table->unsignedBigInteger('set_by_user_id')->nullable()
                  ->comment('Agent ayant acté le changement (si module users présent)');
            $table->dateTime('set_at');
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index(['case_id','status','set_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_status_history');
    }
};
