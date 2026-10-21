<?php

use App\Support\WordImport\BekasyarImport;
use Illuminate\Database\Migrations\Migration;

// Deliberately outside automatic deployment migrations. Only the reviewed command
// supplies this request after approval and a fresh verified backup.
return new class extends Migration
{
    public function up(): void
    {
        $request = app('shelf.bekasyar.reviewed-request');
        app(BekasyarImport::class)->apply($request['manifest'], $request['actor']);
    }

    public function down(): void
    {
        $request = app('shelf.bekasyar.reviewed-request');
        app(BekasyarImport::class)->reverse($request['manifest']);
    }
};
