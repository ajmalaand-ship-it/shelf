<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('category_collection', function (Blueprint $table): void {
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'collection_id']);
        });
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('book_type', 16)->default('poetry')->index();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
        Schema::table('poems', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
            $table->softDeletes();
            // Retain the exact pre-migration ordering for rollback.
            $table->unsignedInteger('foundation_original_order')->nullable();
        });
        DB::table('poems')->update(['foundation_original_order' => DB::raw('sort_order')]);
        DB::table('collections')->orderBy('id')->each(function ($book): void {
            $position = 0;
            foreach (DB::table('poems')->where('collection_id', $book->id)->orderBy('sort_order')->orderBy('id')->get(['id']) as $item) {
                DB::table('poems')->where('id', $item->id)->update([
                    'sort_order' => ++$position,
                    'slug' => 'content-'.$item->id,
                ]);
            }
        });
        Schema::table('poems', fn (Blueprint $table) => $table->unique(['collection_id', 'sort_order']));
        DB::table('app_settings')->where('key', 'content_version')->increment('value');
    }

    public function down(): void
    {
        Schema::table('poems', fn (Blueprint $table) => $table->dropUnique(['collection_id', 'sort_order']));
        DB::table('poems')->whereNotNull('foundation_original_order')->update(['sort_order' => DB::raw('foundation_original_order')]);
        Schema::table('poems', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'deleted_at', 'foundation_original_order']);
        });
        Schema::dropIfExists('category_collection');
        Schema::dropIfExists('categories');
        Schema::table('collections', function (Blueprint $table): void {
            $table->dropIndex(['book_type']);
        });
        // Older SQLite rebuilds parent tables for dropColumn. Disable foreign
        // keys outside a transaction so that rebuild cannot cascade into content.
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('collections', fn (Blueprint $table) => $table->dropColumn(['book_type', 'deleted_at', 'created_by', 'updated_by']));
        });
        DB::table('app_settings')->where('key', 'content_version')->increment('value');
    }
};
