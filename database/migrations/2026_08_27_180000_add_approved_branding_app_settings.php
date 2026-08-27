<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'public_app_name', 'value' => 'پېڅوَل', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'public_slogan', 'value' => 'اجمل اند بشپړه شاعري', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->whereIn('key', ['public_app_name', 'public_slogan'])
            ->delete();
    }
};
