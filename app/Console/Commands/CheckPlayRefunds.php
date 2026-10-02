<?php

namespace App\Console\Commands;

use App\Models\PlayRefundRun;
use App\Services\Play\VoidedPurchaseService;
use App\Support\Staging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckPlayRefunds extends Command
{
    protected $signature = 'shelf:check-play-refunds {--status : Read-only audit/schedule status} {--simulate-after-backup : Synthetic staging check; all rows rolled back}';
    protected $description = 'Read Google voided purchases, append matched refunds and revoke only their book access.';

    public function handle(VoidedPurchaseService $service): int
    {
        if ($this->option('simulate-after-backup')) { return $this->simulate($service); }
        if ($this->option('status')) {
            $this->info('Production refund polling: '.(! Staging::active() && config('play_refunds.enabled') ? 'enabled' : 'disabled'));
            foreach (['Latest check' => PlayRefundRun::latest('id')->first(),
                'Latest successful check' => PlayRefundRun::where('status', 'completed')->latest('id')->first()] as $label => $run) {
                $this->line($label.': '.($run ? $run->finished_at->utc()->toIso8601String().' '.$run->status.' HTTP '.($run->http_status ?? 'unknown')
                    .' seen='.$run->seen.' refunded='.$run->refunded.' duplicate='.$run->duplicate : 'not run'));
            }
            return self::SUCCESS;
        }
        if (Staging::active() || ! config('play_refunds.enabled')) {
            $this->error('Production Google refund polling is disabled here.');
            return self::FAILURE;
        }
        $lock = Cache::lock('shelf-google-refund-check', 900);
        if (! $lock->get()) { $this->line('Refund check skipped: another check is running.'); return self::SUCCESS; }
        try {
            $result = $service->run();
            $this->line(now()->utc()->toIso8601String().' Google refund check '.json_encode($result));
            return $result['status'] === 'completed' ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable) {
            $this->error('Refund check failed safely; private details withheld. Check the audit log.');
            return self::FAILURE;
        } finally { $lock->release(); }
    }
    private function simulate(VoidedPurchaseService $service): int
    {
        if (! Staging::active() && ! app()->runningUnitTests()) {
            $this->error('Synthetic refund checks belong on staging or the temporary test database.');
            return self::FAILURE;
        }
        $db = \Illuminate\Support\Facades\DB::connection();
        $tables = ['readers', 'collections', 'poems', 'app_settings', 'purchases', 'purchase_events', 'sales_ledger', 'book_entitlements', 'play_purchase_references', 'play_refund_runs'];
        $snapshot = function () use ($db, $tables): array {
            $out = [];
            foreach ($tables as $table) { $out[$table] = hash('sha256', json_encode($db->table($table)->orderBy('id')->get())); }
            return $out;
        };
        $before = $snapshot();
        $level = $db->transactionLevel();
        $db->beginTransaction();
        $passed = false;
        try {
            $tag = bin2hex(random_bytes(8));
            $reader = \App\Models\Reader::create(['email' => 'voided-'.$tag.'@example.test']);
            $book = \App\Models\Collection::create(['title' => 'Synthetic refund '.$tag, 'status' => 'published']);
            $item = $book->poems()->create(['body' => 'Synthetic protected text', 'excerpt' => '', 'is_active' => true, 'sample_mode' => 'none']);
            $purchase = \App\Models\Purchase::create(['reader_id' => $reader->id, 'collection_id' => $book->id,
                'store' => 'PLAY_STORE', 'environment' => 'SANDBOX', 'transaction_id' => 'GPA.synthetic-'.$tag,
                'product_id' => $book->product_id, 'purchased_at' => now()->subMinute()]);
            \App\Models\SalesLedger::create(['purchase_id' => $purchase->id, 'entry_key' => 'sale:'.$purchase->id,
                'status' => 'sale', 'currency' => 'USD', 'amount' => '2.99', 'earnings_status' => 'test', 'occurred_at' => $purchase->purchased_at]);
            $db->table('book_entitlements')->insert(['reader_id' => $reader->id, 'collection_id' => $book->id, 'active' => true]);
            $row = ['orderId' => $purchase->transaction_id, 'purchaseToken' => 'synthetic-token-'.$tag,
                'purchaseTimeMillis' => (string) $purchase->purchased_at->getTimestampMs(), 'voidedTimeMillis' => (string) now()->getTimestampMs(),
                'voidedSource' => 0, 'voidedReason' => 1];
            if ($service->apply($row) !== 'refunded' || $service->apply($row) !== 'duplicate') { throw new \RuntimeException; }
            unset($row['orderId']);
            if ($service->apply($row) !== 'duplicate') { throw new \RuntimeException; }
            if (\App\Models\SalesLedger::withTestPurchases()->where('purchase_id', $purchase->id)->count() !== 2
                || \App\Models\SalesLedger::where('purchase_id', $purchase->id)->exists()
                || app(\App\Services\BookAccessService::class)->ownsBook($reader, $book)) { throw new \RuntimeException; }
            $request = \Illuminate\Http\Request::create('/api/library', 'GET');
            $request->setUserResolver(fn () => $reader);
            $controller = app(\App\Http\Controllers\LibraryController::class);
            $data = $controller->index($request, app(\App\Services\Purchases\PurchaseService::class))->getData(true);
            if ($data['books'] !== []) { throw new \RuntimeException; }
            try { $controller->poem($request, $item); throw new \RuntimeException; }
            catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {}
            $passed = true;
        } catch (\Throwable) {
            $this->error('Synthetic refund check failed; no private details printed.');
        } finally { while ($db->transactionLevel() > $level) { $db->rollBack(); } }
        if ($snapshot() !== $before) {
            $this->error('Refund simulation rollback fingerprint mismatch; stop.');
            return self::FAILURE;
        }
        if (! $passed) { return self::FAILURE; }
        $this->info('PASS: staged order/token refund, duplicate protection, Test excluded from income, Library empty and paid text denied; all synthetic rows rolled back and prior fingerprints unchanged.');
        return self::SUCCESS;
    }

}
