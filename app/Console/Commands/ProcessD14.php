<?php
namespace App\Console\Commands;

use App\Models\Reader;
use App\Services\Accounts\{AccountActions, ProviderDeletionWorker};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessD14 extends Command
{
    protected $signature = 'shelf:d14-process {journal : Private verified current journal JSON}';
    protected $description = 'Reapply confirmed deletion intents and retry provider metadata removal';
    public function handle(): int
    {
        abort_if(\App\Support\Staging::active(), 503);
        $path = $this->argument('journal');
        abort_unless(str_starts_with($path, '/home/shelf/tmp/shelf-d14-') && is_file($path)
            && ! is_link($path) && (fileperms($path) & 0077) === 0, 503);
        $records = json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
        foreach ($records as $record) {
            if (! isset($record['deleted_at'])) { continue; }
            $reader = Reader::find($record['reader_id']);
            if ($reader && $reader->created_at->equalTo($record['created_at'])) {
                app(AccountActions::class)->delete($reader);
            } elseif (! $reader) {
                DB::table('provider_deletions')->insertOrIgnore(['reader_id' => $record['reader_id'],
                    'receipt_id' => $record['record_id'], 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        $this->line(json_encode(app(ProviderDeletionWorker::class)->run()));
        return self::SUCCESS;
    }
}
