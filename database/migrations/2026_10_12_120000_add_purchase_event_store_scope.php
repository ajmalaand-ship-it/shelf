<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_events', function (Blueprint $table): void {
            $table->string('store', 20)->default('PLAY_STORE');
            $table->string('app_id')->nullable();
            $table->unsignedBigInteger('event_timestamp_ms')->nullable();
        });
        Schema::table('purchases', fn (Blueprint $table) => $table->unsignedBigInteger('store_purchased_at_ms')->nullable());
        Schema::table('purchase_consents', fn (Blueprint $table) => $table->string('store', 20)->default('PLAY_STORE'));
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('purchase_events')->where('store', 'APP_STORE')->exists()
            || \Illuminate\Support\Facades\DB::table('purchases')->where('store', 'APP_STORE')->exists()
            || \Illuminate\Support\Facades\DB::table('purchase_consents')->where('store', 'APP_STORE')->exists()) {
            throw new RuntimeException('Apple history exists; rollback needs an approved preservation plan.');
        }
        Schema::table('purchase_events', fn (Blueprint $table) => $table->dropColumn(['store', 'app_id', 'event_timestamp_ms']));
        Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn('store_purchased_at_ms'));
        Schema::table('purchase_consents', fn (Blueprint $table) => $table->dropColumn('store'));
    }
};
