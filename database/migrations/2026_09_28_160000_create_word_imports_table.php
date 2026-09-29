<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_imports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            // Keep provenance if a book or account is later permanently removed.
            $table->unsignedBigInteger('collection_id')->index();
            $table->unsignedBigInteger('imported_by');
            $table->string('imported_by_name');
            $table->timestamp('imported_at');
            $table->text('original_filename');
            $table->string('source_path')->unique();
            $table->char('sha256', 64);
            $table->unsignedInteger('item_count');
            $table->json('item_ids');
            $table->json('warnings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_imports');
    }
};
