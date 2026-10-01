<?php

namespace App\Console\Commands;

use App\Services\StagingCatalogue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportStagingCatalogue extends Command
{
    protected $signature = 'shelf:export-staging-catalogue {output}';
    protected $description = 'Export only catalogue tables and one owner for a private staging refresh.';
    public function handle(StagingCatalogue $catalogue): int
    {
        $path = $this->argument('output');
        if (! str_starts_with($path, '/home/shelf/tmp/') || ! is_dir(dirname($path))
            || realpath(dirname($path)) !== dirname($path) || file_exists($path)) {
            $this->error('Use a new private output file under /home/shelf/tmp.'); return self::FAILURE;
        }
        file_put_contents($path, json_encode($catalogue->capture(DB::connection()), JSON_THROW_ON_ERROR));
        chmod($path, 0600);
        $this->info('Catalogue-only export saved privately.'); return self::SUCCESS;
    }
}
