<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_sample_flags', function (Blueprint $table): void {
            $table->unsignedBigInteger('poem_id')->primary();
            $table->boolean('is_free_sample');
        });
        Schema::table('poems', function (Blueprint $table): void {
            $table->string('sample_mode')->default('none')->index();
            $table->string('sample_unit')->nullable();
            $table->unsignedInteger('sample_count')->nullable();
        });
        DB::transaction(function (): void {
            foreach (DB::table('poems')->get(['id', 'is_free_sample']) as $item) {
                DB::table('legacy_sample_flags')->insert(['poem_id' => $item->id, 'is_free_sample' => $item->is_free_sample]);
            }
            DB::table('poems')->update(['is_free_sample' => false, 'sample_mode' => 'none', 'sample_unit' => null, 'sample_count' => null]);
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('legacy_sample_flags')->get() as $item) {
                DB::table('poems')->where('id', $item->poem_id)->update(['is_free_sample' => $item->is_free_sample]);
            }
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
        Schema::table('poems', fn (Blueprint $table) => $table->dropIndex(['sample_mode']));
        Schema::table('poems', fn (Blueprint $table) => $table->dropColumn(['sample_mode', 'sample_unit', 'sample_count']));
        Schema::dropIfExists('legacy_sample_flags');
    }
};
