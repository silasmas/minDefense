<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('card_verifications', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->foreignId('veteran_id')->constrained('veterans')->cascadeOnDelete();
      $table->string('ip', 64)->nullable();
      $table->string('ua')->nullable();
      $table->timestamp('verified_at')->useCurrent();
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('card_verifications'); }
};
