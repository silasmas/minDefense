<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('state_asset_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('state_assets')->cascadeOnDelete();

            $table->enum('event_type', [
                'created','updated','maintenance','inspection','transfer','status_change','note',
            ])->index();

            $table->text('notes')->nullable();
            $table->decimal('cost', 14, 2)->nullable();
            $table->char('currency', 3)->nullable();

            $table->dateTime('occurred_at')->nullable()->index();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('state_asset_logs');
    }
};
