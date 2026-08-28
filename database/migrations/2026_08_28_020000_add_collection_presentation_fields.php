<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('author')->nullable()->after('description');
            $table->text('dedication')->nullable()->after('author');
            $table->longText('introduction')->nullable()->after('dedication');
            $table->text('publication_info')->nullable()->after('introduction');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->dropColumn(['author', 'dedication', 'introduction', 'publication_info']);
        });
    }
};
