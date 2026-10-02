<?php

namespace Tests\Feature;

use App\Models\{Collection, PlayPurchaseReference, PlayRefundRun, Purchase, PurchaseEvent, Reader, SalesLedger};
use App\Services\Play\{GooglePlayClient, VoidedPurchaseService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan, DB, Http, Log};
use Tests\TestCase;

class VoidedPurchasesTest extends TestCase
{
    use RefreshDatabase;
    private Reader $reader;
    private Collection $book;
    private Purchase $purchase;
    private array $row;

    protected function setUp(): void
    {
        parent::setUp();
        config(['reader_auth.enabled' => true, 'play_refunds.enabled' => true, 'play_sync.enabled' => false]);
        $key = openssl_pkey_new(['private_key_bits' => 2048]);
        openssl_pkey_export($key, $private);
        $path = env('SHELF_TEST_TMP').'/synthetic-refund-key.json';
        file_put_contents($path, json_encode(['type' => 'service_account', 'client_email' => 'synthetic@example.test', 'private_key' => $private]));
        chmod($path, 0600);
        config(['play_sync.credentials_path' => $path]);
        $this->reader = Reader::create(['email' => 'synthetic-refund@example.test', 'email_verified_at' => now()]);
        $this->book = Collection::create(['title' => 'Synthetic refund book', 'status' => 'published']);
        $this->book->poems()->create(['body' => 'Sample and paid text', 'excerpt' => '', 'is_active' => true, 'sample_mode' => 'none']);
        $this->purchase = $this->sale($this->reader, $this->book, 'GPA.synthetic-1');
        $this->row = $this->void($this->purchase);
        Log::spy();
        Http::preventStrayRequests();
        $this->responses(['voidedPurchases' => [$this->row]]);
    }
    private function sale(Reader $reader, Collection $book, string $order, string $environment = 'SANDBOX'): Purchase
    {
        $purchase = Purchase::create(['reader_id' => $reader->id, 'collection_id' => $book->id,
            'store' => 'PLAY_STORE', 'environment' => $environment, 'transaction_id' => $order,
            'product_id' => $book->product_id, 'purchased_at' => now()->subMinute()]);
        SalesLedger::create(['purchase_id' => $purchase->id, 'entry_key' => 'sale:'.$purchase->id, 'status' => 'sale',
            'currency' => 'USD', 'amount' => '2.99', 'occurred_at' => $purchase->purchased_at]);
        DB::table('book_entitlements')->updateOrInsert(['reader_id' => $reader->id, 'collection_id' => $book->id], ['active' => true]);
        return $purchase;
    }
    private function void(Purchase $purchase): array
    {
        return ['orderId' => $purchase->transaction_id, 'purchaseToken' => 'synthetic-token-'.$purchase->id,
            'purchaseTimeMillis' => (string) $purchase->purchased_at->getTimestampMs(),
            'voidedTimeMillis' => (string) now()->getTimestampMs(), 'voidedSource' => 0, 'voidedReason' => 1];
    }
    private function responses(array $body): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']),
            'androidpublisher.googleapis.com/*' => Http::response($body)]);
    }
    private function runCheck(): array { return app(VoidedPurchaseService::class)->run(); }
    private function bearer(): array
    {
        app('auth')->forgetGuards();
        return ['Authorization' => 'Bearer '.$this->reader->createToken('synthetic', ['reader'])->plainTextToken];
    }
    public function test_exact_order_refund_duplicate_token_fallback_test_income_and_download_removal_on_next_library_check(): void
    {
        $before = $this->purchase->toArray();
        $result = $this->runCheck();
        $this->assertSame('completed', $result['status']);
        $this->assertSame(1, $result['refunded']);
        $this->assertSame(200, $result['http_status']);
        $this->assertSame(1, $this->runCheck()['duplicate']);
        unset($this->row['orderId']);
        $this->responses(['voidedPurchases' => [$this->row]]);
        $this->assertSame(1, $this->runCheck()['duplicate']);
        $this->assertDatabaseCount('purchase_events', 1);
        $this->assertDatabaseCount('sales_ledger', 2);
        $this->assertDatabaseHas('sales_ledger', ['entry_key' => 'refund:'.$this->purchase->id, 'status' => 'refund', 'amount' => '-2.990000', 'earnings_status' => 'test']);
        $this->assertSame(0, SalesLedger::count());
        $this->assertSame('Test', SalesLedger::withTestPurchases()->latest('id')->first()->income_mode);
        $this->assertEquals($before, $this->purchase->fresh()->toArray());
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->reader->id, 'collection_id' => $this->book->id, 'active' => false]);
        // Refunded-only Library succeeds even when RevenueCat is disabled/unavailable.
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books')->assertJsonStructure(['offline_valid_until']);
        $this->getJson('/api/library/poems/'.$this->book->poems()->first()->id, $this->bearer())->assertNotFound();
        $this->assertDatabaseHas('play_purchase_references', ['purchase_token_hash' => hash('sha256', $this->row['purchaseToken'])]);
        $this->assertStringNotContainsString($this->row['purchaseToken'], json_encode(DB::table('play_purchase_references')->get()));
        $this->assertStringNotContainsString($this->row['purchaseToken'], json_encode(DB::table('purchase_events')->get()));
        Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/services.shelf.app/purchases/voidedpurchases') && $r['type'] === 0 && $r['maxResults'] === 1000);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'onetimeproducts'));
    }
    public function test_refund_only_affects_that_order_book_and_reader_and_real_amounts_stay_separate(): void
    {
        $other = Reader::create(['email' => 'other-synthetic@example.test']);
        $this->sale($other, $this->book, 'GPA.other-owner');
        $another = Collection::create(['title' => 'Other synthetic book', 'status' => 'withdrawn']);
        $real = $this->sale($this->reader, $another, 'GPA.real-synthetic', 'PRODUCTION');
        $this->responses(['voidedPurchases' => [$this->row, $this->void($real)]]);
        $this->assertSame(2, $this->runCheck()['refunded']);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $other->id, 'collection_id' => $this->book->id, 'active' => true]);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->reader->id, 'collection_id' => $another->id, 'active' => false]);
        $this->assertEquals(0, SalesLedger::sum('amount'));
        $this->assertSame('Real', SalesLedger::where('entry_key', 'refund:'.$real->id)->first()->income_mode);
        $this->assertSame('unknown', SalesLedger::where('entry_key', 'refund:'.$real->id)->first()->earnings_status);
    }
    public function test_other_valid_purchase_of_same_book_keeps_access(): void
    {
        $this->sale($this->reader, $this->book, 'GPA.another-valid-order');
        $this->assertSame(1, $this->runCheck()['refunded']);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->reader->id, 'collection_id' => $this->book->id, 'active' => true]);
    }
    public function test_previously_received_provider_refund_does_not_double_money_entry(): void
    {
        SalesLedger::create(['purchase_id' => $this->purchase->id, 'entry_key' => 'refund:'.$this->purchase->id, 'status' => 'refund', 'currency' => 'USD', 'amount' => '-2.99', 'occurred_at' => now()]);
        $this->assertSame(1, $this->runCheck()['duplicate']);
        $this->assertDatabaseCount('sales_ledger', 2);
        $this->assertDatabaseHas('book_entitlements', ['active' => false]);
    }
    public function test_unmatched_void_is_retried_when_sale_arrives_later(): void
    {
        $late = array_replace($this->row, ['orderId' => 'GPA.late-arrival', 'purchaseToken' => 'synthetic-late']);
        $this->responses(['voidedPurchases' => [$late]]);
        $this->assertSame(1, $this->runCheck()['unmatched']);
        $this->assertDatabaseCount('play_purchase_references', 0);
        $this->sale($this->reader, $this->book, 'GPA.late-arrival');
        $this->assertSame(1, $this->runCheck()['refunded']);
    }
    public function test_ambiguous_order_and_mismatched_time_never_revoke_or_bind_tokens(): void
    {
        $this->sale($this->reader, $this->book, $this->purchase->transaction_id, 'PRODUCTION');
        $this->assertSame('warning', $this->runCheck()['status']);
        $this->assertDatabaseCount('purchase_events', 0);
        $this->assertDatabaseCount('play_purchase_references', 0);
        $this->assertDatabaseHas('book_entitlements', ['active' => true]);
        $this->responses(['voidedPurchases' => [array_replace($this->row, ['orderId' => 'unknown'])]]);
        $this->assertSame(1, $this->runCheck()['unmatched']);
    }
    public function test_known_token_cannot_be_moved_to_another_order_and_wrong_purchase_time_is_held(): void
    {
        $this->runCheck();
        $book = Collection::create(['title' => 'Protected second book']);
        $purchase = $this->sale($this->reader, $book, 'GPA.second');
        $this->responses(['voidedPurchases' => [array_replace($this->void($purchase), ['purchaseToken' => $this->row['purchaseToken']])]]);
        $this->assertSame(1, $this->runCheck()['conflict']);
        $this->responses(['voidedPurchases' => [array_replace($this->void($purchase), ['purchaseTimeMillis' => (string) now()->subHours(2)->getTimestampMs()])]]);
        $this->assertSame(1, $this->runCheck()['conflict']);
        $this->assertDatabaseHas('book_entitlements', ['collection_id' => $book->id, 'active' => true]);
        $this->assertDatabaseCount('play_purchase_references', 1);
    }
    public function test_pagination_empty_history_and_exact_continuation_token(): void
    {
        $seq = Http::sequence()->push(['voidedPurchases' => [$this->row], 'tokenPagination' => ['nextPageToken' => 'synthetic-page']])->push([]);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']), 'androidpublisher.googleapis.com/*' => $seq]);
        $result = $this->runCheck();
        $this->assertSame(2, $result['pages']);
        $this->assertSame('completed', $result['status']);
        Http::assertSent(fn ($r) => $r->method() === 'GET' && ($r->data()['token'] ?? null) === 'synthetic-page');
    }
    public function test_denials_busy_provider_and_network_error_are_logged_without_revoking_anything(): void
    {
        foreach ([401, 403, 429, 503] as $code) {
            Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']), 'androidpublisher.googleapis.com/*' => Http::response(['error' => 'synthetic-secret-provider-body'], $code)]);
            $result = $this->runCheck();
            $this->assertSame('failed', $result['status']);
            $this->assertSame($code, $result['http_status']);
        }
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']), 'androidpublisher.googleapis.com/*' => Http::failedConnection()]);
        $this->assertSame('failed', $this->runCheck()['status']);
        $this->assertDatabaseCount('purchase_events', 0);
        $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseHas('book_entitlements', ['active' => true]);
        $this->assertStringNotContainsString('synthetic-secret-provider-body', json_encode(PlayRefundRun::all()));
        Log::shouldHaveReceived('log')->withArgs(fn ($level, $message, $data) => $level === 'warning' && ! str_contains(json_encode($data), 'synthetic-secret'));
    }
    public function test_malformed_or_partial_page_and_pagination_loop_fail_closed(): void
    {
        foreach ([['voidedPurchases' => [$this->row, []]], ['voidedPurchases' => [array_replace($this->row, ['voidedQuantity' => 1])]], ['error' => 'invalid']] as $body) {
            $this->responses($body);
            $this->assertSame('failed', $this->runCheck()['status']);
            $this->assertDatabaseCount('purchase_events', 0);
        }
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']), 'androidpublisher.googleapis.com/*' => Http::sequence()->push(['tokenPagination' => ['nextPageToken' => 'loop']])->push(['tokenPagination' => ['nextPageToken' => 'loop']])]);
        $this->assertSame('failed', $this->runCheck()['status']);
        $this->assertDatabaseHas('book_entitlements', ['active' => true]);
    }
    public function test_failure_on_later_page_keeps_only_earlier_verified_refund_and_can_retry(): void
    {
        $seq = Http::sequence()->push(['voidedPurchases' => [$this->row], 'tokenPagination' => ['nextPageToken' => 'later']])->push([], 503);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['oauth2.googleapis.com/*' => Http::response(['access_token' => 'synthetic-oauth']), 'androidpublisher.googleapis.com/*' => $seq]);
        $result = $this->runCheck();
        $this->assertSame('failed', $result['status']);
        $this->assertSame(1, $result['refunded']);
        $this->assertSame(503, $result['http_status']);
        $this->responses(['voidedPurchases' => [$this->row]]);
        $this->assertSame(1, $this->runCheck()['duplicate']);
        $this->assertDatabaseCount('sales_ledger', 2);
    }
    public function test_deleted_reader_refund_keeps_financial_history(): void
    {
        DB::table('book_entitlements')->delete();
        $this->reader->delete();
        $this->assertSame(1, $this->runCheck()['refunded']);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('sales_ledger', 2);
        $this->assertDatabaseCount('book_entitlements', 0);
    }
    public function test_staging_blocks_real_google_calls_even_if_enabled_and_simulation_rolls_back(): void
    {
        config(['staging.testing' => true]);
        Http::fake();
        $this->artisan('shelf:check-play-refunds')->assertFailed();
        try { app(GooglePlayClient::class)->voidedPurchases(); $this->fail('Staging contacted Google'); }
        catch (\App\Services\Play\PlaySyncException) {}
        Http::assertNothingSent();
        $exit = Artisan::call('shelf:check-play-refunds', ['--simulate-after-backup' => true]);
        $this->assertSame(0, $exit, Artisan::output());
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseCount('play_purchase_references', 0);
        $root = env('SHELF_TEST_TMP').'/refund-runner'; mkdir($root.'/scripts', 0700, true);
        file_put_contents($root.'/.shelf-staging', 'synthetic');
        copy(base_path('scripts/run_play_refunds.sh'), $root.'/scripts/run_play_refunds.sh');
        $process = new \Symfony\Component\Process\Process(['bash', $root.'/scripts/run_play_refunds.sh']);
        $process->mustRun(); $this->assertStringContainsString('polling disabled', $process->getOutput());
    }
    public function test_schema_rollback_is_reversible_when_empty_and_retains_audit_history_once_used(): void
    {
        $migration = require database_path('migrations/2026_10_02_020000_create_play_refund_audit.php');
        $migration->down(); $migration->up();
        $this->runCheck();
        try { $migration->down(); $this->fail('Removed refund audit'); }
        catch (\RuntimeException) { $this->assertDatabaseCount('play_refund_runs', 1); }
    }
}
