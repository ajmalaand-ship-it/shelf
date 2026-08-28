<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poems', function (Blueprint $table): void {
            $table->string('title')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Real untitled poetry cannot safely be coerced back into a required title.
    }
};
