<?php
namespace App\Services\Accounts;

use App\Models\Reader;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DeletionJournal
{
    public function claim(\App\Models\Purchase $purchase, Reader $target, \App\Models\User $owner, string $case, string $proof): array
    {
        return $this->publish(['reader_id' => $target->id, 'created_at' => $target->created_at->toIso8601String(),
            'claimed_at' => now()->toIso8601String(), 'record_id' => bin2hex(random_bytes(16)),
            'purchase_id' => $purchase->id, 'transaction_hash' => hash('sha256', $purchase->transaction_id),
            'owner_id' => $owner->id, 'case_reference' => $case, 'proof_hash' => $proof]);
    }

    public function record(Reader $reader): array
    {
        // Staging and isolated tests never write to production's journal/OneDrive.
        abort_if(\App\Support\Staging::active(), 503, 'Account deletion is unavailable on the test copy.');
        $record = ['reader_id' => $reader->id, 'created_at' => $reader->created_at->toIso8601String(),
            'deleted_at' => now()->toIso8601String(), 'record_id' => bin2hex(random_bytes(16))];
        return $this->publish($record);
    }

    private function publish(array $record): array
    {
        abort_if(\App\Support\Staging::active(), 503);
        $process = new Process(['python3', '-B', base_path('scripts/shelf_deletion_journal.py'), 'append'], base_path(),
            ['TMPDIR' => '/home/shelf/tmp', 'TMP' => '/home/shelf/tmp', 'TEMP' => '/home/shelf/tmp', 'GNUPGHOME' => '/home/shelf/secrets/backup/gnupg'], json_encode($record), 120);
        try {
            $process->mustRun();
            $verified = json_decode($process->getOutput(), true, 16, JSON_THROW_ON_ERROR);
            abort_unless(($verified['reader_id'] ?? null) === $record['reader_id'] && ($verified['created_at'] ?? null) === $record['created_at'], 503);
            return $verified;
        } catch (\Throwable) {
            // No process output or identifying data enters application logs.
            abort(503, 'Deletion could not be safely recorded. Please try again.');
        }
    }
}
