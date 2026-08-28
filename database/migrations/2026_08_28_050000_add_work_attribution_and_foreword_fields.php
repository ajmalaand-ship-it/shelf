<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('foreword_author')->nullable()->after('introduction');
            $table->longText('foreword')->nullable()->after('foreword_author');
        });

        Schema::table('poems', function (Blueprint $table): void {
            $table->string('work_type', 32)->default('ORIGINAL')->after('excerpt');
            $table->string('original_author')->nullable()->after('work_type');
            $table->string('translator')->nullable()->after('original_author');
            $table->string('source_date_place')->nullable()->after('translator');
            $table->text('source_note')->nullable()->after('source_date_place');
            $table->index(['collection_id', 'work_type']);
        });
    }

    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->dropIndex(['collection_id', 'work_type']);
            $table->dropColumn(['work_type', 'original_author', 'translator', 'source_date_place', 'source_note']);
        });

        Schema::table('collections', function (Blueprint $table): void {
            $table->dropColumn(['foreword_author', 'foreword']);
        });
    }
};
