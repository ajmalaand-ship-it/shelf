<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->string('layout_mode', 20)->default('SOURCE')->after('source_note');
        });
    }

    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->dropColumn('layout_mode');
        });
    }
};
