<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_price_changes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('collection_id')->index();
            $table->decimal('old_price_usd', 10, 2)->nullable();
            $table->decimal('new_price_usd', 10, 2)->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('actor');
            $table->string('reason');
            $table->timestamp('created_at');
        });
        Schema::create('play_product_syncs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_id')->unique()->constrained('collections')->cascadeOnDelete();
            $table->unsignedBigInteger('revision')->default(1);
            $table->string('status')->default('pending');
            $table->string('message')->default('Waiting for Google Play setup.');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->decimal('synced_price_usd', 10, 2)->nullable();
            $table->boolean('synced_active')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('play_product_syncs');
        Schema::dropIfExists('book_price_changes');
    }
};
