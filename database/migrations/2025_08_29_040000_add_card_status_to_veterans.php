<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('veterans', function (Blueprint $table) {
      $table->string('card_status', 12)->nullable()->after('card_expires_at');   // active|revoked|lost
      $table->timestamp('card_revoked_at')->nullable()->after('card_status');
      $table->string('card_status_reason')->nullable()->after('card_revoked_at');
    });
  }
  public function down(): void {
    Schema::table('veterans', function (Blueprint $table) {
      $table->dropColumn(['card_status','card_revoked_at','card_status_reason']);
    });
  }
};
