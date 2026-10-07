<?php
namespace App\Services\Accounts;

use App\Services\Purchases\RevenueCatClient;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ProviderDeletionWorker
{
    public function run(): array
    {
        abort_if(\App\Support\Staging::active(), 503);
        $results = ['completed' => 0, 'pending' => 0, 'exceptions' => 0];
        foreach (DB::table('provider_deletions')->whereNull('completed_at')->orderBy('reader_id')->limit(100)->get() as $job) {
            // A journal intent might precede the committing deletion. Never scrub a live identity.
            if (DB::table('readers')->where('id', $job->reader_id)->exists()) { continue; }
            DB::table('provider_deletions')->where('reader_id', $job->reader_id)->update([
                'attempts' => DB::raw('attempts + 1'), 'last_attempt_at' => now(), 'updated_at' => now()]);
            try {
                app(RevenueCatClient::class)->eraseMetadata($job->reader_id);
                DB::table('provider_deletions')->where('reader_id', $job->reader_id)->update([
                    'status' => 'completed', 'exception_code' => null, 'completed_at' => now(), 'updated_at' => now()]);
                $results['completed']++;
            } catch (\Throwable $error) {
                $conflict = $error instanceof HttpExceptionInterface && $error->getStatusCode() === 409;
                DB::table('provider_deletions')->where('reader_id', $job->reader_id)->update([
                    'status' => $conflict ? 'review_required' : 'pending',
                    'exception_code' => $conflict ? 'immutable_provider_metadata' : 'provider_unverified', 'updated_at' => now()]);
                $results[$conflict ? 'exceptions' : 'pending']++;
            }
        }
        return $results;
    }
}
