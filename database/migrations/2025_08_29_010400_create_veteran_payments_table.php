<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historique des paiements liés à un ancien combattant
        Schema::create('veteran_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('veteran_id')->constrained('veterans')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('veteran_cases')->nullOnDelete();

            $table->enum('payment_type', ['pension','arrears','aid'])->default('pension');

            // Pour les pensions mensuelles, stocker une période (mois) — ex: 2025-08-01
            $table->date('period_month')->nullable()->comment('Mois de référence, ex: 2025-08-01');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');

            $table->enum('status', ['scheduled','paid','failed','refunded'])->default('paid');
            $table->dateTime('paid_at')->nullable();

            $table->string('reference', 128)->nullable()->index()
                  ->comment('Référence bancaire / mobile money / pièce comptable');

            $table->text('notes')->nullable();

            $table->timestamps();
$table->softDeletes();
            // Anti-doublon: 1 paiement mensuel par type et par vétéran
            $table->unique(['veteran_id','payment_type','period_month'], 'uq_vet_month_type');

            $table->index(['veteran_id','paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veteran_payments');
    }
};
