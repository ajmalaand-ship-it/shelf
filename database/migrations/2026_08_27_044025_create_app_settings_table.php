<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            ['key' => 'content_version', 'value' => '1'],
            ['key' => 'min_app_version', 'value' => '1.0.0'],
            ['key' => 'unlock_all_product_id', 'value' => ''],
            ['key' => 'unlock_all_entitlement', 'value' => 'unlock_all'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
