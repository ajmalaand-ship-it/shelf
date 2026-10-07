<?php
namespace Tests\Feature;

use App\Models\{Collection, Purchase, Reader, SalesLedger, User};
use App\Services\Accounts\{AccountActions, DeletionJournal, ProviderDeletionWorker};
use App\Services\Purchases\{PurchaseService, PurchaseRecoveryService, RevenueCatClient};
use App\Services\Play\GooglePlayClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http};
use Tests\TestCase;

class D14PolicyTest extends TestCase
{
    use RefreshDatabase;
    private Reader $original;
    private Reader $target;
    private Purchase $purchase;
    private array $google;
    private array $subscriber;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['purchases.enabled' => true, 'purchases.secret_key' => 'synthetic', 'purchases.secret_key_path' => null,
            'purchases.webhook_authorization' => 'synthetic', 'purchases.app_id' => 'synthetic', 'purchases.public_sdk_key' => 'goog_synthetic']);
        $this->original = Reader::create(['email' => 'deleted@example.test']);
        $this->target = Reader::create(['email' => 'new@example.test']);
        $this->target->forceFill(['email_verified_at' => now()])->save();
        $book = Collection::create(['title' => 'Synthetic', 'status' => 'published', 'price_usd' => '2.99']);
        $this->purchase = Purchase::create(['reader_id' => $this->original->id, 'collection_id' => $book->id,
            'store' => 'PLAY_STORE', 'environment' => 'SANDBOX', 'product_id' => $book->product_id,
            'transaction_id' => 'GPA.synthetic', 'purchased_at' => now()->startOfSecond()]);
        SalesLedger::create(['purchase_id' => $this->purchase->id, 'entry_key' => 'sale:'.$this->purchase->id,
            'status' => 'sale', 'occurred_at' => now(), 'currency' => 'USD', 'amount' => '2.99',
            'earnings_status' => 'test', 'agreement_snapshot' => ['immutable' => 'synthetic']]);
        $this->owner = User::factory()->create(['is_owner' => true]);
        $this->google = ['orderId' => 'GPA.synthetic', 'purchaseStateContext' => ['purchaseState' => 'PURCHASED'],
            'acknowledgementState' => 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED', 'testPurchaseContext' => ['fopType' => 'TEST'],
            'purchaseCompletionTime' => $this->purchase->purchased_at->toIso8601String(),
            'productLineItem' => [['productId' => $book->product_id, 'productOfferDetails' => ['quantity' => 1,
                'refundableQuantity' => 1, 'consumptionState' => 'CONSUMPTION_STATE_YET_TO_BE_CONSUMED']]]];
        $this->subscriber = ['original_app_user_id' => (string) $this->original->id, 'subscriber_attributes' => [],
            'entitlements' => [$book->product_id => ['product_identifier' => $book->product_id, 'expires_date' => null]],
            'non_subscriptions' => [$book->product_id => [['store' => 'play_store', 'is_sandbox' => true,
                'purchase_date' => $this->purchase->purchased_at->toIso8601String()]]]];
        $this->mock(DeletionJournal::class, function ($mock) {
            $mock->shouldReceive('record')->andReturn(['record_id' => str_repeat('a', 32)]);
            $mock->shouldReceive('claim')->andReturn(['record_id' => str_repeat('b', 32)]);
        });
    }
    private function provider(): void
    {
        $google = $this->google;
        $this->mock(GooglePlayClient::class, fn ($mock) => $mock->shouldReceive('productPurchase')->with('synthetic-private-token')->andReturn($google));
        Http::fake(['api.revenuecat.com/*' => Http::response(['subscriber' => $this->subscriber])]);
    }
    private function recover(bool $verified = true, ?User $actor = null): void
    {
        app(PurchaseRecoveryService::class)->recover($actor ?? $this->owner, $this->target, $this->purchase->id,
            'synthetic-private-token', 'CASE_123', $verified);
    }
    public function test_deletion_erases_profile_access_tokens_but_keeps_immutable_money_and_retry(): void
    {
        $this->original->createToken('synthetic');
        app(PurchaseService::class)->refreshAccess($this->original, $this->purchase->book);
        $history = SalesLedger::withTestPurchases()->first()->toArray();
        app(AccountActions::class)->delete($this->original);
        $this->assertDatabaseMissing('readers', ['id' => $this->original->id]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('book_entitlements', 0);
        $this->assertSame($history, SalesLedger::withTestPurchases()->first()->toArray());
        $this->assertDatabaseHas('provider_deletions', ['reader_id' => $this->original->id, 'status' => 'pending']);
    }
    public function test_missing_durable_journal_does_not_erase_reader(): void
    {
        $this->mock(DeletionJournal::class, fn ($mock) => $mock->shouldReceive('record')->andThrow(new \RuntimeException('synthetic unavailable')));
        try { app(AccountActions::class)->delete($this->original); $this->fail(); } catch (\RuntimeException) {}
        $this->assertDatabaseHas('readers', ['id' => $this->original->id]);
        $this->assertDatabaseCount('provider_deletions', 0);
    }
    public function test_owner_recovery_preserves_history_restores_and_refund_revokes(): void
    {
        $before = $this->purchase->fresh()->toArray();
        app(AccountActions::class)->delete($this->original);
        $this->provider();
        $this->recover();
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->target->id, 'active' => true]);
        $this->assertSame($before, $this->purchase->fresh()->toArray());
        app(PurchaseService::class)->reconcile($this->target);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->target->id, 'active' => true]);
        app(PurchaseService::class)->recordGoogleRefund($this->purchase, ['voidedTimeMillis' => now()->getTimestampMs()], 'order_id');
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->target->id, 'active' => false]);
        $this->assertDatabaseCount('purchase_recoveries', 1);
        $this->assertSame(0, SalesLedger::count());
        $this->assertSame(['immutable' => 'synthetic'], SalesLedger::withTestPurchases()->where('status','sale')->first()->agreement_snapshot);
    }
    public function test_google_order_and_token_checks_are_read_only_and_connection_errors_hide_tokens(): void
    {
        config(['play_sync.package' => 'services.shelf.app']);
        $play = new GooglePlayClient;
        (new \ReflectionProperty($play, 'token'))->setValue($play, 'synthetic-access-token');
        Http::fake(['androidpublisher.googleapis.com/*' => Http::sequence()
            ->push(['orderId' => 'GPA.synthetic', 'state' => 'PROCESSED', 'purchaseToken' => 'synthetic-private-token',
                'lineItems' => [['productId' => $this->purchase->product_id]]])
            ->push($this->google)]);
        $token = $play->orderPurchaseToken($this->purchase);
        $this->assertSame('synthetic-private-token', $token);
        $this->assertSame($this->google, $play->productPurchase($token));
        Http::assertNotSent(fn ($request) => $request->method() !== 'GET');
        Http::swap(new \Illuminate\Http\Client\Factory); Http::preventStrayRequests();
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('synthetic-private-token'));
        try { $play->productPurchase($token); $this->fail(); }
        catch (\App\Services\Play\PlaySyncException $error) { $this->assertStringNotContainsString($token, $error->getMessage()); }
    }

    public function test_recorded_order_lookup_avoids_requesting_device_tokens_from_readers(): void
    {
        app(AccountActions::class)->delete($this->original);
        $google = $this->google;
        $this->mock(GooglePlayClient::class, function ($mock) use ($google) {
            $mock->shouldReceive('orderPurchaseToken')->once()->andReturn('synthetic-private-token');
            $mock->shouldReceive('productPurchase')->once()->with('synthetic-private-token')->andReturn($google);
        });
        Http::fake(['api.revenuecat.com/*' => Http::response(['subscriber' => $this->subscriber])]);
        app(PurchaseRecoveryService::class)->recover($this->owner, $this->target, $this->purchase->id, '', 'CASE_456', true);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->target->id, 'active' => true]);
        $this->assertFalse(RevenueCatClient::productionEnabled());
    }

    public function test_duplicate_recovery_does_not_grant_second_account(): void
    {
        app(AccountActions::class)->delete($this->original); $this->provider(); $this->recover();
        try { $this->recover(); $this->fail(); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertDatabaseCount('purchase_recoveries', 1);
    }
    public function test_email_matching_and_non_owner_are_insufficient(): void
    {
        app(AccountActions::class)->delete($this->original);
        $this->target->forceFill(['email' => 'deleted@example.test'])->save();
        foreach ([[false, $this->owner, 422], [true, User::factory()->create(['is_owner' => false]), 403]] as [$verified, $actor, $status]) {
            try { $this->recover($verified, $actor); $this->fail(); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame($status, $e->getStatusCode()); }
        }
        $this->assertDatabaseCount('purchase_recoveries', 0); Http::assertNothingSent();
    }
    public function test_unknown_pending_refunded_or_wrong_provider_purchase_never_unlocks(): void
    {
        app(AccountActions::class)->delete($this->original);
        $valid = $this->google;
        foreach ([['purchaseStateContext' => ['purchaseState' => 'PENDING']], ['orderId' => 'GPA.wrong'],
            ['testPurchaseContext' => ['fopType' => 'UNKNOWN']], ['productLineItem' => []], ['purchaseCompletionTime' => 'unparseable']] as $change) {
            $this->google = array_replace($valid, $change); $this->provider();
            try { $this->recover(); $this->fail(); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        }
        $this->assertDatabaseCount('purchase_recoveries', 0); $this->assertDatabaseCount('book_entitlements', 0);
    }
    public function test_recovery_rejects_recorded_refund_before_provider_calls(): void
    {
        app(AccountActions::class)->delete($this->original);
        app(PurchaseService::class)->recordGoogleRefund($this->purchase, ['voidedTimeMillis' => now()->getTimestampMs()], 'order_id');
        try { $this->recover(); $this->fail(); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertDatabaseCount('purchase_recoveries', 0); Http::assertNothingSent();
    }
    public function test_metadata_provider_failure_is_retryable_without_losing_history(): void
    {
        app(AccountActions::class)->delete($this->original);
        Http::fake(['api.revenuecat.com/*' => Http::response([], 503)]);
        $this->assertSame(1, app(ProviderDeletionWorker::class)->run()['pending']);
        $this->assertDatabaseHas('provider_deletions', ['status' => 'pending', 'attempts' => 1]);
        Http::swap(new \Illuminate\Http\Client\Factory); Http::preventStrayRequests();
        Http::fake(['api.revenuecat.com/*' => Http::response(['subscriber' => $this->subscriber])]);
        $this->assertSame(1, app(ProviderDeletionWorker::class)->run()['completed']);
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('sales_ledger', 1);
    }
    public function test_metadata_deletion_is_verified_and_whole_customer_is_never_deleted(): void
    {
        app(AccountActions::class)->delete($this->original);
        $before = $this->subscriber; $before['subscriber_attributes'] = ['$email' => ['value'=>'synthetic@example.test', 'updated_at_ms'=>1]];
        Http::fakeSequence()->push(['subscriber'=>$before])->push([])->push(['subscriber'=>$this->subscriber]);
        $this->assertSame(1, app(ProviderDeletionWorker::class)->run()['completed']);
        Http::assertSent(fn ($request) => $request->method()==='POST' && $request['attributes']['$email']['value'] === null);
        Http::assertNotSent(fn ($request) => $request->method()==='DELETE');
    }
    public function test_immutable_provider_metadata_requires_review_without_destructive_delete(): void
    {
        app(AccountActions::class)->delete($this->original);
        $this->subscriber['subscriber_attributes'] = ['$ip'=>['value'=>'192.0.2.1','updated_at_ms'=>1]];
        Http::fake(['api.revenuecat.com/*'=>Http::response(['subscriber'=>$this->subscriber])]);
        $this->assertSame(1, app(ProviderDeletionWorker::class)->run()['exceptions']);
        $this->assertDatabaseHas('provider_deletions',['status'=>'review_required','exception_code'=>'immutable_provider_metadata']);
        Http::assertNotSent(fn ($request)=>$request->method()!=='GET');
        $this->assertDatabaseCount('purchases', 1);
    }
}
