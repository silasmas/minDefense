// database/migrations/2025_08_29_060000_add_decline_to_veteran_verifications.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('veteran_verifications', function (Blueprint $table) {
            $table->dateTime('declined_at')->nullable()->after('consumed_at');
            $table->text('decline_reason')->nullable()->after('declined_at');
        });
    }
    public function down(): void {
        Schema::table('veteran_verifications', function (Blueprint $table) {
            $table->dropColumn(['declined_at','decline_reason']);
        });
    }
};
