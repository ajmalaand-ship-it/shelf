<?php

// Committed reversible migration template. The operator instantiates a new
// dated migration for each refresh, with catalogue-only before/after snapshots.
return new class extends \Illuminate\Database\Migrations\Migration
{
    private function data(string $name): array
    {
        return json_decode(file_get_contents(__DIR__.'/'.$name.'.json'), true, 512, JSON_THROW_ON_ERROR);
    }
    public function up(): void { app(\App\Services\StagingCatalogue::class)->apply($this->data('after')); }
    public function down(): void { app(\App\Services\StagingCatalogue::class)->apply($this->data('before')); }
};
