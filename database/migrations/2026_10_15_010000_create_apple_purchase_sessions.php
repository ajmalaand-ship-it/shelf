<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_purchase_sessions', function (Blueprint $table): void {
            $table->id();
            // Retain attribution after account deletion; never cascade purchase evidence.
            $table->unsignedBigInteger('reader_id')->index();
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('consent_id');
            $table->string('provider_identity', 80)->unique();
            $table->unsignedBigInteger('issued_at_ms');
            $table->unsignedBigInteger('expires_at_ms');
            $table->unsignedBigInteger('revoked_at_ms')->nullable();
            $table->string('transaction_id')->nullable()->unique();
            $table->unsignedBigInteger('store_purchased_at_ms')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('apple_purchase_sessions')->exists()) {
            throw new RuntimeException('Apple session evidence exists; rollback needs an approved preservation plan.');
        }
        Schema::dropIfExists('apple_purchase_sessions');
    }
};
