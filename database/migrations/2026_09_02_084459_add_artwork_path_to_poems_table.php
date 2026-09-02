<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('poems', function (Blueprint $table) {
            $table->string('artwork_path')->nullable()->after('layout_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table) {
            $table->dropColumn('artwork_path');
        });
    }
};
