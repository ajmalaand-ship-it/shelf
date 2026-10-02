<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_purchase_references', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('purchase_id')->unique()->constrained()->restrictOnDelete();
            $t->string('purchase_token_hash', 64)->unique();
            $t->timestamp('created_at');
        });
        Schema::create('play_refund_runs', function (Blueprint $t): void {
            $t->id();
            $t->string('status', 20);
            $t->unsignedSmallInteger('http_status')->nullable();
            $t->unsignedInteger('pages')->default(0);
            $t->unsignedInteger('seen')->default(0);
            $t->unsignedInteger('refunded')->default(0);
            $t->unsignedInteger('duplicate')->default(0);
            $t->unsignedInteger('unmatched')->default(0);
            $t->unsignedInteger('conflict')->default(0);
            $t->string('error_code', 40)->nullable();
            $t->timestamp('started_at');
            $t->timestamp('finished_at');
            $t->timestamp('created_at');
        });
    }
    public function down(): void
    {
        if (DB::table('play_purchase_references')->exists() || DB::table('play_refund_runs')->exists()) {
            throw new RuntimeException('Refund audit history exists; preserve it before schema rollback.');
        }
        Schema::dropIfExists('play_refund_runs');
        Schema::dropIfExists('play_purchase_references');
    }
};
