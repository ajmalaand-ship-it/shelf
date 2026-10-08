<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('reviewer_book_grants', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('reader_id')->constrained()->cascadeOnDelete();
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->string('purpose');
            $t->string('authorized_by');
            $t->timestamp('granted_at');
            $t->timestamp('revoked_at')->nullable();
            $t->unique(['reader_id', 'collection_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('reviewer_book_grants'); }
};
