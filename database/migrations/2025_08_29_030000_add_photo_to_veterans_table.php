<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('veterans', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('email');
            $table->string('photo_disk', 30)->default('public')->after('photo_path');
        });
    }
    public function down(): void {
        Schema::table('veterans', function (Blueprint $table) {
            $table->dropColumn(['photo_path','photo_disk']);
        });
    }
};
