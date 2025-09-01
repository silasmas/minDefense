<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('veterans', function (Blueprint $table) {
      $table->string('card_number', 32)->nullable()->after('rank');
      $table->date('card_expires_at')->nullable()->after('card_number');
    });
  }
  public function down(): void {
    Schema::table('veterans', function (Blueprint $table) {
      $table->dropColumn(['card_number','card_expires_at']);
    });
  }
};
