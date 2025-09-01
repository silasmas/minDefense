<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('owner_type', 20)->default('user'); // user | veteran
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('phone', 30)->index();
            $table->string('purpose', 30); // password_reset | login
            $table->string('code', 6);
            $table->dateTime('expires_at');
            $table->dateTime('consumed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->index(['owner_type','owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
