<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->json('presentation_spacing')->nullable()->after('layout_mode');
        });
    }

    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->dropColumn('presentation_spacing');
        });
    }
};
