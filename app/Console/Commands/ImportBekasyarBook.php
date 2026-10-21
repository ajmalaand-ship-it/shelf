<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\WordImport\BekasyarImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

class ImportBekasyarBook extends Command
{
    protected $signature = 'shelf:import-bekasyar {manifest} {archive} {--approved-sha256=} {--actor=} {--apply} {--reverse}';
    protected $description = 'Preview or explicitly apply the reviewed 183-poem Draft 13 import';

    public function handle(BekasyarImport $service): int
    {
        $manifest = $service->load($this->argument('manifest'), $this->argument('archive'), (string) $this->option('approved-sha256'));
        $already = $service->inspect($manifest);
        $this->info('Draft 13: 183 poems (48 + 47 + 77 + 11). Original/bin content is protected.');
        if (! $this->option('apply')) {
            $this->info($already ? 'Already imported exactly; no changes.' : 'Dry run only; no changes.');
            return self::SUCCESS;
        }
        if ($already && ! $this->option('reverse')) {
            $this->info('Already imported exactly; no changes.');
            return self::SUCCESS;
        }
        if ($this->option('reverse') && ! $already) { throw new \RuntimeException('No intact applied import to reverse.'); }
        $actor = $this->option('actor')
            ? User::whereKey($this->option('actor'))->where('is_owner', true)->firstOrFail()
            : User::where('is_owner', true)->sole();
        Auth::setUser($actor);
        // This process must complete successfully in the same command/run before
        // the migration begins. Existing backup script verifies SQL/ZIP checksums.
        $backup = new Process(['python3', '-B', base_path('scripts/shelf_daily_backup.py')], base_path(), [
            'TMPDIR' => '/home/shelf/tmp', 'TMP' => '/home/shelf/tmp', 'TEMP' => '/home/shelf/tmp',
        ]);
        $backup->setTimeout(600);
        $backup->mustRun();
        $this->line(trim($backup->getOutput()));
        // Recheck under the service's transaction after the backup as well.
        app()->instance('shelf.bekasyar.reviewed-request', ['manifest' => $manifest, 'actor' => $actor]);
        $path = 'scripts/book_import/migrations/'.BekasyarImport::MIGRATION.'.php';
        if ($this->option('reverse')) {
            $batches = app('migration.repository')->getMigrationBatches();
            $batch = $batches[BekasyarImport::MIGRATION] ?? throw new \RuntimeException('Import migration batch missing.');
            return $this->call('migrate:rollback', ['--path' => [$path], '--batch' => $batch, '--force' => true]);
        }
        return $this->call('migrate', ['--path' => [$path], '--force' => true]);
    }
}
