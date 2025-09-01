<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('veteran_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('veteran_id')->constrained('veterans')->cascadeOnDelete();
            $table->string('phone', 30)->index();
            $table->string('token', 64)->unique();         // jeton random
            $table->string('purpose', 32)->default('status_confirm'); // extensible
            $table->enum('next_status', ['draft','recognized','suspended','deceased'])->nullable();
            $table->json('payload')->nullable();           // champs à confirmer éventuellement
            $table->dateTime('expires_at');
            $table->dateTime('consumed_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->string('channel', 16)->default('sms');
            $table->timestamps();
            $table->index(['veteran_id','purpose']);
        });

        Schema::table('veterans', function (Blueprint $table) {
            if (!Schema::hasColumn('veterans','phone_verified_at')) {
                $table->dateTime('phone_verified_at')->nullable()->after('phone');
            }
        });
    }

    public function down(): void {
        Schema::dropIfExists('veteran_verifications');
        if (Schema::hasColumn('veterans','phone_verified_at')) {
            Schema::table('veterans', fn (Blueprint $t) => $t->dropColumn('phone_verified_at'));
        }
    }
};
