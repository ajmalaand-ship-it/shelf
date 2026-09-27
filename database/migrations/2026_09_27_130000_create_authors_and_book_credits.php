<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_latin')->nullable();
            $table->text('biography')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('collection_author', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->enum('role', ['author', 'translator', 'editor']);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['collection_id', 'author_id', 'role']);
            $table->index(['collection_id', 'position']);
        });
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('language', 16)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->dropIndex(['language']);
            $table->dropColumn('language');
        });
        Schema::dropIfExists('collection_author');
        Schema::dropIfExists('authors');
    }
};
