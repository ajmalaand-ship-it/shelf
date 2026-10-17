<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Accounting\FinancialRetention;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Authorized owner rendering in memory; no login/session/account or metadata writes. */
class CheckFinancialRetention extends Command
{
    protected $signature = 'shelf:check-financial-retention';
    protected $description = 'Read-only retention report, real Admin route rendering and disabled-gate checks';

    public function handle(): int
    {
        foreach (['purchases.production_enabled', 'purchases.apple_sandbox_enabled', 'purchases.apple_owned_book3_test_enabled', 'play_sync.enabled'] as $flag) {
            if (config($flag)) { throw new \RuntimeException('Required disabled control is enabled: '.$flag); }
        }
        $report = app(FinancialRetention::class)->report();
        $count = DB::table('financial_retention_records')->count();
        config(['session.driver' => 'array']);
        $owner = User::where('is_owner', true)->firstOrFail();
        Auth::onceUsingId($owner->id);
        $request = Request::create(rtrim(config('app.url'), '/').'/admin/financial-retention');
        if (\App\Support\Staging::active()) {
            $request->headers->set('X-Shelf-Test-Key', config('staging.access_key'));
        }
        $kernel = app(Kernel::class);
        $response = $kernel->handle($request);
        $html = $response->getContent();
        if ($response->getStatusCode() !== 200 || ! str_contains($html, 'Automatic expiry and deletion are OFF') || ! str_contains($html, 'Evidence history') || ! str_contains($html, 'Record filed return')) {
            throw new \RuntimeException('Real Admin route did not render the retention interface.');
        }
        if (DB::table('financial_retention_records')->count() !== $count) {
            throw new \RuntimeException('Read-only interface check changed retention evidence.');
        }
        $this->info('PASS: actual owner Admin route rendered with report, evidence history and filing actions; in-memory session only.');
        $this->info('PASS: real checkout, Apple sandbox gates and price sync OFF; no disposal action or expiry job.');
        $this->info('Retention report: '.count($report['rows']).' purchase groups; '.$report['eligible'].' eligible for preservation review; '.$report['blocked'].' blocked/retained; '.$count.' metadata entries.');
        return self::SUCCESS;
    }
}
