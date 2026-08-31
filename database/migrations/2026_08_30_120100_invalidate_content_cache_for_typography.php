<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->where('key', 'content_version')->increment('value');
    }

    public function down(): void
    {
        // Cache invalidations are monotonic and must not be reversed.
    }
};
