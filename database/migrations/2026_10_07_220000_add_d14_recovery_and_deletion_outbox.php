<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_deletions', function (Blueprint $table) {
            $table->unsignedBigInteger('reader_id')->primary(); // No profile or foreign key.
            $table->string('receipt_id', 32)->unique();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('exception_code')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_recoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('reader_id')->index(); // Retained minimal audit after deletion.
            $table->unsignedBigInteger('owner_id');
            $table->string('case_reference', 80);
            $table->string('proof_hash', 64);
            $table->string('journal_id', 32)->unique();
            $table->timestamp('created_at');
        });
        Schema::create('purchase_recovery_claims', function (Blueprint $table) {
            $table->foreignId('purchase_id')->primary()->constrained()->restrictOnDelete();
            $table->foreignId('reader_id')->constrained('readers')->cascadeOnDelete();
            $table->foreignId('recovery_id')->constrained('purchase_recoveries')->restrictOnDelete();
        });
    }
    public function down(): void
    {
        // Never silently discard completed recovery/deletion audit on rollback.
        if (\Illuminate\Support\Facades\DB::table('purchase_recoveries')->exists()
            || \Illuminate\Support\Facades\DB::table('provider_deletions')->exists()) {
            throw new RuntimeException('Retained D14 evidence requires an owner-approved preservation plan before rollback.');
        }
        Schema::dropIfExists('purchase_recovery_claims');
        Schema::dropIfExists('purchase_recoveries');
        Schema::dropIfExists('provider_deletions');
    }
};
