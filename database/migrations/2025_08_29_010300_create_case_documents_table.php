<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pièces jointes d’un dossier (stockage simple : disque + chemin)
        Schema::create('case_documents', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('case_id')->constrained('veteran_cases')->cascadeOnDelete();

            $table->string('doc_type', 50)->nullable()->comment('Ex: CNI, preuve_service, rapport_medical');
            $table->string('storage_disk', 50)->default('local');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->dateTime('uploaded_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['case_id','doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_documents');
    }
};
