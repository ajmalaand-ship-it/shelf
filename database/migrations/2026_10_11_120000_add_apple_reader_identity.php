<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('readers', function (Blueprint $table) {
            $table->string('apple_subject_hash', 64)->nullable()->unique();
            $table->text('apple_refresh_token')->nullable();
        });
        Schema::create('apple_login_challenges', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('nonce_hash', 64);
            $table->timestamp('expires_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_login_challenges');
        Schema::table('readers', function (Blueprint $table) {
            $table->dropUnique(['apple_subject_hash']);
            $table->dropColumn(['apple_subject_hash', 'apple_refresh_token']);
        });
    }
};
