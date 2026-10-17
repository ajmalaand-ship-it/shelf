<?php

namespace Tests\Feature;

use App\Models\{Collection, Purchase, PurchaseEvent, Reader, SalesLedger, User};
use App\Services\Purchases\{AppleSandboxPurchases, PurchaseRecoveryService, PurchaseService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http};
use Tests\TestCase;

class AppleSandboxPurchasesTest extends TestCase
{
    use RefreshDatabase;
    private Reader $reader;
    private Collection $book;
    private array $event;
    private array $proof;
    private array $appProof;
    private array $productProof;
    private array $entitlement;
    private int $httpStatus = 200;
    private bool $hideApplePurchase = false;

    protected function setUp(): void
    {
        parent::setUp();
        config(['reader_auth.enabled' => true, 'purchases.apple_sandbox_enabled' => true,
            'purchases.apple_public_sdk_key' => 'appl_synthetic', 'purchases.apple_app_id' => 'appAppleFixture',
            'purchases.apple_product_id' => 'prodAppleFixture', 'purchases.apple_project_id' => 'projFixture',
            'purchases.apple_webhook_authorization' => 'synthetic-apple-auth',
            'purchases.metadata_key' => 'synthetic-v2', 'purchases.metadata_key_path' => null,
            'purchases.production_enabled' => false, 'purchases.enabled' => false]);
        $this->reader = Reader::create(['email' => 'owner@example.test']);
        $this->reader->forceFill(['email_verified_at' => now()])->save();
        User::factory()->create(['email' => 'owner@example.test', 'is_owner' => true]);
        $this->book = new Collection([ 'title' => 'Synthetic Apple book', 'status' => 'published', 'price_usd' => '2.99']);
        $this->book->forceFill(['id' => 3])->save();
        $this->book->poems()->create(['body' => "Sample\nPaid text", 'excerpt' => '', 'is_active' => true,
            'sample_mode' => 'partial', 'sample_unit' => 'lines', 'sample_count' => 1]);
        DB::table('purchase_consents')->insert(['reader_id' => $this->reader->id, 'collection_id' => 3,
            'store' => 'APP_STORE', 'wording' => PurchaseService::CONSENT, 'created_at' => now()->subMinute()]);
        $ms = now()->getTimestampMs();
        $this->bindSession($ms);
        $this->event = ['id' => 'event-1', 'type' => 'NON_RENEWING_PURCHASE', 'environment' => 'SANDBOX',
            'store' => 'APP_STORE', 'app_id' => 'appAppleFixture', 'app_user_id' => 'shelf_apple_'.str_repeat('a', 64),
            'product_id' => 'shelf_book_3', 'transaction_id' => '100000000000001',
            'purchased_at_ms' => $ms, 'event_timestamp_ms' => $ms, 'currency' => 'USD', 'price_in_purchased_currency' => '2.99'];
        $this->proof = ['object' => 'purchase', 'id' => 'purchFixture', 'customer_id' => 'shelf_apple_'.str_repeat('a', 64),
            'original_customer_id' => 'shelf_apple_'.str_repeat('a', 64), 'product_id' => 'prodAppleFixture',
            'store' => 'app_store', 'environment' => 'sandbox', 'ownership' => 'purchased', 'quantity' => 1,
            'store_purchase_identifier' => $this->event['transaction_id'], 'purchased_at' => $ms, 'status' => 'owned'];
        $this->appProof = ['object' => 'app', 'id' => 'appAppleFixture', 'project_id' => 'projFixture',
            'type' => 'app_store', 'app_store' => ['bundle_id' => 'services.shelf.app']];
        $this->productProof = ['id' => 'prodAppleFixture', 'app_id' => 'appAppleFixture',
            'store_identifier' => 'shelf_book_3', 'type' => 'one_time', 'one_time' => ['is_consumable' => false]];
        $this->entitlement = ['object' => 'entitlement', 'state' => 'active', 'project_id' => 'projFixture', 'lookup_key' => 'shelf_book_3'];
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            $url = $request->url();
            $data = match (true) {
                str_contains($url, '/apps/') => $this->appProof,
                str_contains($url, '/products/') => $this->productProof,
                str_contains($url, '/entitlements') => $this->listing([$this->entitlement]),
                default => $this->listing($this->hideApplePurchase ? [] : [$this->proof]),
            };
            return Http::response($data, $this->httpStatus);
        });
    }


    private function bindSession(int $ms): void
    {
        DB::table('apple_purchase_sessions')->updateOrInsert(['provider_identity' => 'shelf_apple_'.str_repeat('a', 64)], [
            'reader_id' => $this->reader->id, 'collection_id' => 3,
            'consent_id' => DB::table('purchase_consents')->where('store', 'APP_STORE')->orderByDesc('id')->value('id'),
            'issued_at_ms' => $ms - 60000, 'expires_at_ms' => $ms + 840000,
            'revoked_at_ms' => null, 'transaction_id' => null,
        ]);
    }

    private function listing(array $items): array { return ['object' => 'list', 'items' => $items, 'next_page' => null]; }
    private function bearer(?Reader $reader = null): array { app('auth')->forgetGuards(); return ['Authorization' => 'Bearer '.($reader ?? $this->reader)->createToken('synthetic')->plainTextToken]; }
    private function webhook(array $changes = []) { return $this->postJson('/api/purchases/webhook', ['event' => array_replace($this->event, $changes)], ['Authorization' => 'synthetic-apple-auth']); }
    private function refund(string $id = 'refund', int $offset = 1000) {
        $this->proof['status'] = 'refunded';
        return $this->webhook(['id' => $id, 'type' => 'CANCELLATION', 'event_timestamp_ms' => $this->event['purchased_at_ms'] + $offset]);
    }
    private function reversal(string $id = 'reverse', int $offset = 2000) {
        $this->proof['status'] = 'owned';
        return $this->webhook(['id' => $id, 'type' => 'REFUND_REVERSED', 'event_timestamp_ms' => $this->event['purchased_at_ms'] + $offset]);
    }
    private function active(): bool { return (bool) DB::table('book_entitlements')->where('reader_id', $this->reader->id)->where('collection_id', 3)->value('active'); }

    public function test_default_off_and_staging_forced_off_never_publish_a_key_or_accept_checkout(): void
    {
        config(['purchases.apple_sandbox_enabled' => false]);
        $this->getJson('/api/purchases/config?platform=ios')->assertJsonPath('enabled', false)->assertJsonPath('public_sdk_key', null)->assertJsonPath('production_checkout_enabled', false);
        $this->webhook()->assertStatus(503);
        $this->postJson('/api/library/books/3/consent', ['agree' => true, 'purchase_session_version' => 1, 'checkout_mode' => 'sandbox', 'store' => 'APP_STORE'], $this->bearer())->assertStatus(503);
        config(['purchases.apple_sandbox_enabled' => true, 'staging.testing' => true]);
        $this->assertFalse(\App\Services\Purchases\ApplePurchaseProof::configured());
        Http::assertNothingSent();
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_owner_only_exact_book_and_mode_consent_without_changing_android_configuration(): void
    {
        $this->getJson('/api/purchases/config?platform=ios')->assertJsonPath('public_sdk_key', 'appl_synthetic')->assertJsonPath('allowed_product_ids.0', 'shelf_book_3')->assertJsonPath('production_checkout_enabled', false);
        $this->getJson('/api/purchases/config')->assertJsonPath('enabled', false);
        $data = ['agree' => true, 'purchase_session_version' => 1, 'checkout_mode' => 'sandbox', 'store' => 'APP_STORE'];
        $this->postJson('/api/library/books/3/consent', $data, $this->bearer())->assertOk()->assertJsonPath('store', 'APP_STORE');
        $this->postJson('/api/library/books/3/consent', array_replace($data, ['checkout_mode' => 'production']), $this->bearer())->assertUnprocessable();
        $other = Reader::create(['email' => 'owner+other@example.test']); $other->forceFill(['email_verified_at' => now()])->save();
        $this->postJson('/api/library/books/3/consent', $data, $this->bearer($other))->assertForbidden();
        $otherBook = new Collection([ 'title' => 'Other book', 'status' => 'published', 'price_usd' => '2.99']); $otherBook->forceFill(['id' => 4])->save();
        $this->postJson('/api/library/books/4/consent', $data, $this->bearer())->assertStatus(409);
        $this->bindSession($this->event['purchased_at_ms']);
        $this->reader->forceFill(['buying_blocked' => true])->save();
        $this->webhook()->assertForbidden();
    }

    public function test_verified_sale_is_idempotent_test_only_and_paid_access_is_account_isolated(): void
    {
        $this->webhook()->assertOk();
        $this->postJson('/api/purchases/webhook', ['event' => array_reverse($this->event, true)], ['Authorization' => 'synthetic-apple-auth'])->assertOk();
        $this->webhook(['id' => 'duplicate-sale'])->assertOk();
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('purchase_events', 2);
        $this->assertSame(1, SalesLedger::withTestPurchases()->count()); $this->assertSame(0, SalesLedger::count());
        $this->assertSame('Test', SalesLedger::withTestPurchases()->first()->income_mode); $this->assertTrue($this->active());
        $this->getJson('/api/library/books/'.$this->book->slug, $this->bearer())->assertOk();
        $other = Reader::create(['email' => 'other@example.test']); $other->forceFill(['email_verified_at' => now()])->save();
        $this->getJson('/api/library/books/'.$this->book->slug, $this->bearer($other))->assertNotFound();
        $this->proof['customer_id'] = $this->proof['original_customer_id'] = (string) $other->id;
        $this->webhook(['id' => 'transfer', 'app_user_id' => (string) $other->id])->assertStatus(409);
        $this->webhook(['currency' => 'EUR'])->assertStatus(409);
        $this->assertSame('2.990000', SalesLedger::withTestPurchases()->first()->amount);
    }

    public function test_invalid_provider_proof_never_grants_or_records_a_purchase(): void
    {
        $valid = $this->proof;
        foreach (['environment' => 'production', 'store' => 'play_store', 'customer_id' => '999', 'original_customer_id' => '999',
            'product_id' => 'wrong', 'ownership' => 'family_shared', 'quantity' => 2, 'store_purchase_identifier' => '100000000000002', 'purchased_at' => $valid['purchased_at'] + 1] as $field => $value) {
            $this->proof = array_replace($valid, [$field => $value]); $this->webhook()->assertStatus(503);
            $this->assertDatabaseCount('purchases', 0);
        }
        $this->proof = $valid; $this->entitlement['state'] = 'inactive'; $this->webhook()->assertStatus(503);
        $this->entitlement['state'] = 'active'; $this->appProof['app_store']['bundle_id'] = 'other.app'; $this->webhook()->assertStatus(503);
        $this->appProof['app_store']['bundle_id'] = 'services.shelf.app'; $this->productProof['one_time']['is_consumable'] = true; $this->webhook()->assertStatus(503);
        $this->assertDatabaseCount('purchase_events', 0); $this->assertDatabaseCount('sales_ledger', 0);
    }

    public function test_invalid_event_environment_app_book_auth_and_missing_apple_consent_fail_closed(): void
    {
        $this->webhook(['environment' => 'PRODUCTION'])->assertUnprocessable();
        $this->webhook(['app_id' => 'playApp'])->assertUnprocessable();
        $this->webhook(['product_id' => 'shelf_book_4'])->assertUnprocessable();
        $this->postJson('/api/purchases/webhook', ['event' => $this->event], ['Authorization' => 'wrong'])->assertUnauthorized();
        DB::table('purchase_consents')->where('store', 'APP_STORE')->delete();
        DB::table('purchase_consents')->insert(['reader_id' => $this->reader->id, 'collection_id' => 3, 'wording' => PurchaseService::CONSENT, 'created_at' => now()->subMinute()]);
        $this->webhook()->assertStatus(409); $this->assertDatabaseCount('purchases', 0);

    }

    public function test_refund_reversal_and_repeat_cycles_append_without_rewriting_or_real_income(): void
    {
        $this->webhook()->assertOk(); $sale = SalesLedger::withTestPurchases()->first()->toArray();
        $this->refund()->assertOk(); $this->refund()->assertOk(); $this->refund('duplicate-refund', 1500)->assertOk();
        $this->assertFalse($this->active()); $this->assertSame(2, SalesLedger::withTestPurchases()->count());
        $this->getJson('/api/library/books/'.$this->book->slug, $this->bearer())->assertNotFound();
        $this->reversal()->assertOk(); $this->reversal()->assertOk(); $this->assertTrue($this->active());
        $this->webhook(['id' => 'late-sale'])->assertOk();
        $this->refund('second-refund', 3000)->assertOk(); $this->reversal('second-reversal', 4000)->assertOk();
        $this->assertTrue($this->active()); $this->assertSame(5, SalesLedger::withTestPurchases()->count());
        $this->assertSame($sale, SalesLedger::withTestPurchases()->first()->toArray()); $this->assertSame(0, SalesLedger::count());
        $this->assertEquals(2.99, SalesLedger::withTestPurchases()->sum('amount'));
    }

    public function test_reversal_without_refund_or_after_revocation_and_stale_corrections_are_rejected(): void
    {
        $this->reversal()->assertStatus(409); $this->webhook()->assertOk(); $this->reversal()->assertStatus(409);
        $this->refund()->assertOk(); $this->reversal()->assertOk();
        $this->refund('stale-refund', 500)->assertStatus(409); $this->assertTrue($this->active());
        $this->proof['status'] = 'refunded';
        $this->webhook(['id' => 'revoke', 'type' => 'EXPIRATION', 'event_timestamp_ms' => $this->event['purchased_at_ms'] + 3000])->assertOk();
        $this->reversal('invalid-reversal', 4000)->assertStatus(409); $this->assertFalse($this->active());
    }

    public function test_store_scoped_transaction_and_event_collisions_cannot_cross_refund(): void
    {
        $play = Purchase::create(['reader_id' => $this->reader->id, 'collection_id' => 3, 'store' => 'PLAY_STORE',
            'environment' => 'SANDBOX', 'transaction_id' => $this->event['transaction_id'], 'product_id' => 'shelf_book_3', 'purchased_at' => now()]);
        PurchaseEvent::create(['provider_event_id' => 'event-1', 'event_type' => 'CANCELLATION', 'reader_id' => $this->reader->id,
            'collection_id' => 3, 'transaction_id' => $this->event['transaction_id'], 'environment' => 'SANDBOX', 'occurred_at' => now(), 'details' => []]);
        $this->webhook()->assertOk(); $this->assertDatabaseCount('purchases', 2); $this->assertTrue($this->active());
        $this->refund()->assertOk();
        $this->assertTrue(AppleSandboxPurchases::active($play));
        $this->assertFalse(AppleSandboxPurchases::active(Purchase::where('store', 'APP_STORE')->first()));
        $this->assertTrue($this->active()); // Existing Play ownership independently keeps this reader's book.
    }

    public function test_restore_and_confirmation_require_server_proof_and_refund_reversal_webhook(): void
    {
        $this->postJson('/api/library/confirm', [], $this->bearer())->assertOk()->assertJsonPath('books.0.id', 3);
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk(); $this->assertDatabaseCount('purchases', 1);
        $this->proof['status'] = 'refunded';
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books'); $this->assertFalse($this->active());
        $this->proof['status'] = 'owned';
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->reversal()->assertOk(); $this->assertTrue($this->active());
        $this->httpStatus = 503;
        $this->getJson('/api/library', $this->bearer())->assertStatus(503)->assertJsonMissing(['offline_valid_until']);
        config(['purchases.apple_sandbox_enabled' => false]);
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books');
    }

    public function test_refund_before_sale_tombstone_and_deleted_account_recovery_remain_fail_closed(): void
    {
        $this->refund()->assertOk(); $this->assertDatabaseCount('purchases', 0);
        $this->proof['status'] = 'owned'; $this->webhook()->assertOk(); $this->assertFalse($this->active());
        $this->reversal()->assertOk(); $this->assertTrue($this->active());
        $purchase = Purchase::first(); $this->reader->delete();
        $this->reversal('deleted-reversal', 3000)->assertForbidden();
        $target = Reader::create(['email' => 'target@example.test']); $target->forceFill(['email_verified_at' => now()])->save();
        try {
            app(PurchaseRecoveryService::class)->recover(User::where('is_owner', true)->first(), $target, $purchase->id, '', 'fixture-case', true);
            $this->fail('Apple recovery must not transfer ownership.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertDatabaseCount('purchase_recovery_claims', 0);
    }

    public function test_multiple_pre_sale_tombstones_are_replayed_in_order_without_unlocking_or_double_refund(): void
    {
        $this->refund()->assertOk();
        $this->webhook(['id' => 'pre-sale-revoke', 'type' => 'EXPIRATION', 'event_timestamp_ms' => $this->event['purchased_at_ms'] + 2000])->assertOk();
        $this->refund('pre-sale-second-refund', 3000)->assertOk();
        $this->proof['status'] = 'owned'; $this->webhook()->assertOk();
        $this->assertFalse($this->active()); $this->assertEquals(0, SalesLedger::withTestPurchases()->sum('amount'));
        $this->reversal('after-revoke', 4000)->assertStatus(409);
        $this->assertSame(0, SalesLedger::count());
    }

    public function test_duplicate_financial_refund_after_revocation_and_ambiguous_provider_results_fail_closed(): void
    {
        $this->webhook()->assertOk(); $this->refund()->assertOk();
        $this->webhook(['id' => 'revoke-after-refund', 'type' => 'EXPIRATION', 'event_timestamp_ms' => $this->event['purchased_at_ms'] + 2000])->assertOk();
        $this->refund('same-refund-later', 3000)->assertOk();
        $this->assertEquals(0, SalesLedger::withTestPurchases()->sum('amount')); $this->assertFalse($this->active());
        Http::swap(new \Illuminate\Http\Client\Factory); Http::preventStrayRequests();
        Http::fake(['api.revenuecat.com/v2/projects/projFixture/apps/*' => Http::response($this->appProof),
            'api.revenuecat.com/v2/projects/projFixture/products/*' => Http::response($this->productProof),
            'api.revenuecat.com/*' => Http::response($this->listing([$this->proof, $this->proof]))]);
        $this->webhook(['id' => 'ambiguous-sale'])->assertStatus(503);
        $this->assertDatabaseCount('purchases', 1);
    }

    public function test_buying_blocked_owner_still_receives_refunds_and_reconciliation_without_new_checkout(): void
    {
        $this->webhook()->assertOk();
        $this->reader->forceFill(['buying_blocked' => true])->save();
        $this->refund()->assertOk(); $this->assertFalse($this->active());
        $this->reversal()->assertOk(); $this->assertTrue($this->active());
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonPath('buying_blocked', true);
        $this->postJson('/api/library/books/3/consent', ['agree' => true, 'purchase_session_version' => 1, 'checkout_mode' => 'sandbox', 'store' => 'APP_STORE'], $this->bearer())->assertForbidden();
    }

    public function test_migration_rollback_is_reversible_before_apple_history_and_preserves_history_afterward(): void
    {
        $migration = require database_path('migrations/2026_10_12_120000_add_purchase_event_store_scope.php');
        DB::table('purchase_consents')->delete();
        $migration->down(); $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('purchase_events', 'store'));
        $migration->up(); DB::table('purchase_consents')->insert(['reader_id' => $this->reader->id, 'collection_id' => 3, 'store' => 'APP_STORE', 'wording' => PurchaseService::CONSENT, 'created_at' => now()->subMinute()]); $this->bindSession($this->event['purchased_at_ms']); $this->webhook()->assertOk();
        try { $migration->down(); $this->fail('Apple history must prevent lossy rollback.'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('preservation plan', $e->getMessage()); }
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('sales_ledger', 1);
    }
    private function existingPlayOwnership(): Purchase
    {
        $play = Purchase::create(['reader_id' => $this->reader->id, 'collection_id' => 3,
            'store' => 'PLAY_STORE', 'environment' => 'SANDBOX', 'transaction_id' => 'play-existing',
            'product_id' => 'shelf_book_3', 'purchased_at' => now()->subDay()]);
        SalesLedger::create(['purchase_id' => $play->id, 'entry_key' => 'sale:'.$play->id,
            'status' => 'sale', 'amount' => '2.99', 'currency' => 'USD', 'occurred_at' => now()->subDay(), 'earnings_status' => 'test']);
        app(PurchaseService::class)->refreshAccess($this->reader, $this->book);
        return $play;
    }

    private function ownedTestConsent(array $changes = [], ?Reader $reader = null, int $book = 3)
    {
        $response = $this->postJson('/api/library/books/'.$book.'/consent', array_replace([
            'agree' => true, 'purchase_session_version' => 1, 'checkout_mode' => 'sandbox', 'store' => 'APP_STORE',
            'owned_book_sandbox_test' => true], $changes), $this->bearer($reader));
        if ($response->status() === 200) {
            $this->event['app_user_id'] = $this->proof['customer_id'] = $this->proof['original_customer_id'] = $response->json('app_user_id');
            $this->event['purchased_at_ms'] = $this->proof['purchased_at'] = now()->getTimestampMs();
        }
        return $response;
    }

    public function test_owned_book_exception_is_default_off_and_normal_duplicate_guard_stays(): void
    {
        $this->existingPlayOwnership();
        $this->assertFalse(config('purchases.apple_owned_book3_test_enabled'));
        $this->ownedTestConsent()->assertStatus(409);
        $this->webhook()->assertStatus(409);
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->ownedTestConsent(['owned_book_sandbox_test' => false])->assertStatus(409);
        $this->ownedTestConsent()->assertOk()->assertJsonPath('owned_book_sandbox_test', true);
        $this->assertTrue($this->active());
        $this->assertDatabaseCount('purchases', 1);
    }

    public function test_owned_test_requires_both_gates_exact_owner_book_store_and_no_real_checkout(): void
    {
        $this->existingPlayOwnership();
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $other = Reader::create(['email' => 'owner+alias@example.test']);
        $other->forceFill(['email_verified_at' => now()])->save();
        $this->ownedTestConsent([], $other)->assertForbidden();
        $this->ownedTestConsent(['store' => 'PLAY_STORE'])->assertUnprocessable();
        $this->ownedTestConsent(['checkout_mode' => 'production'])->assertUnprocessable();
        $otherBook = new Collection(['title' => 'Other', 'status' => 'published', 'price_usd' => '2.99']);
        $otherBook->forceFill(['id' => 4])->save();
        $this->ownedTestConsent([], null, 4)->assertStatus(409);
        foreach (['buying_blocked' => true, 'email_verified_at' => null] as $field => $value) {
            $old = $this->reader->fresh()->$field;
            $this->reader->forceFill([$field => $value])->save();
            $this->ownedTestConsent()->assertForbidden();
            $this->reader->forceFill([$field => $old])->save();
        }
        config(['purchases.production_enabled' => true]);
        $this->ownedTestConsent()->assertStatus(409);
        config(['purchases.production_enabled' => false, 'purchases.apple_sandbox_enabled' => false]);
        $this->ownedTestConsent()->assertStatus(503);
        config(['purchases.apple_sandbox_enabled' => true, 'staging.testing' => true]);
        $this->assertFalse(\App\Services\Purchases\ApplePurchaseProof::configured());
        $this->ownedTestConsent()->assertUnauthorized(); // Production reader tokens cannot cross staging.
        $this->assertDatabaseCount('purchases', 1);
    }

    public function test_owned_test_needs_active_play_sale_and_cannot_use_a_complimentary_or_refunded_purchase(): void
    {
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->ownedTestConsent()->assertStatus(409);
        $play = $this->existingPlayOwnership();
        SalesLedger::create(['purchase_id' => $play->id, 'entry_key' => 'refund:fixture',
            'status' => 'refund', 'occurred_at' => now(), 'earnings_status' => 'test']);
        $this->ownedTestConsent()->assertStatus(409);
    }

    public function test_owned_test_requires_fresh_consent_and_fresh_apple_proof_preserving_play_history(): void
    {
        $play = $this->existingPlayOwnership();
        $original = $play->fresh()->toArray(); $ledger = $play->entries()->first()->toArray();
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->webhook()->assertStatus(409); // Normal Apple consent cannot waive ownership.
        $this->ownedTestConsent()->assertOk();
        $this->proof['environment'] = 'production'; $this->webhook()->assertStatus(503);
        $this->proof['environment'] = 'sandbox';
        DB::table('purchase_consents')->where('wording', \App\Services\Purchases\OwnerAppleSandboxTest::CONSENT)
            ->update(['created_at' => now()->subMinutes(16)]);
        $this->webhook()->assertStatus(409);
        DB::table('purchase_consents')->where('wording', \App\Services\Purchases\OwnerAppleSandboxTest::CONSENT)
            ->update(['created_at' => now()->addMinute()]);
        $this->webhook()->assertStatus(409); // Old Apple proof predating the new consent.
        DB::table('purchase_consents')->where('wording', \App\Services\Purchases\OwnerAppleSandboxTest::CONSENT)
            ->update(['created_at' => now()]);
        $this->webhook()->assertOk(); $this->webhook()->assertOk();
        $this->assertSame($original, $play->fresh()->toArray());
        $this->assertSame($ledger, $play->entries()->first()->toArray());
        $this->assertDatabaseCount('purchases', 2);
        $this->assertSame(2, SalesLedger::withTestPurchases()->where('status', 'sale')->count());
        $this->assertSame(0, SalesLedger::count());
        $this->assertTrue($this->active());
        $this->ownedTestConsent()->assertStatus(409); // A second new Apple transaction is prohibited.
        $this->proof['store_purchase_identifier'] = '100000000000002';
        $this->webhook(['id' => 'second-apple', 'transaction_id' => '100000000000002'])->assertStatus(409);
        $this->proof['store_purchase_identifier'] = $this->event['transaction_id'];
        $this->refund()->assertOk(); $this->assertTrue($this->active());
        $this->assertTrue(AppleSandboxPurchases::active($play));
        $this->reversal()->assertOk(); $this->assertTrue($this->active());
        $this->assertSame($ledger, $play->entries()->first()->toArray());
        $this->assertSame(0, SalesLedger::count());
    }

    public function test_library_reports_separate_apple_confirmation_only_after_independent_proof(): void
    {
        $play = $this->existingPlayOwnership();
        // Keep this test focused on Apple; Play verifier itself is covered separately.
        $provider = \Mockery::mock(\App\Services\Purchases\RevenueCatClient::class);
        $provider->shouldReceive('subscriber')->andReturn([]);
        $provider->shouldReceive('confirms')->andReturn(true);
        $this->app->instance(\App\Services\Purchases\RevenueCatClient::class, $provider);
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->hideApplePurchase = true;
        $this->getJson('/api/library', $this->bearer())->assertOk()
            ->assertJsonPath('apple_owned_book3_test_allowed', true)
            ->assertJsonPath('apple_sandbox_book3_confirmed', false)->assertJsonPath('books.0.id', 3);
        $this->ownedTestConsent()->assertOk();
        $this->hideApplePurchase = false;
        $this->postJson('/api/library/confirm', [], $this->bearer())->assertOk()
            ->assertJsonPath('apple_owned_book3_test_allowed', false)
            ->assertJsonPath('apple_sandbox_book3_confirmed', true)->assertJsonPath('books.0.id', 3);
        $this->refund()->assertOk();
        $this->getJson('/api/library', $this->bearer())->assertOk()
            ->assertJsonPath('apple_sandbox_book3_confirmed', false)->assertJsonPath('books.0.id', 3);
        $this->assertTrue(AppleSandboxPurchases::active($play));
        $this->httpStatus = 503;
        $this->getJson('/api/library', $this->bearer())->assertStatus(503)
            ->assertJsonMissing(['apple_sandbox_book3_confirmed' => true]);
    }

    public function test_session_identity_is_private_random_single_use_and_old_clients_fail_closed(): void
    {
        $data = ['agree' => true, 'checkout_mode' => 'sandbox', 'store' => 'APP_STORE'];
        $this->postJson('/api/library/books/3/consent', $data, $this->bearer())->assertUnprocessable();
        $first = $this->postJson('/api/library/books/3/consent', $data + ['purchase_session_version' => 1], $this->bearer())->assertOk();
        $second = $this->postJson('/api/library/books/3/consent', $data + ['purchase_session_version' => 1], $this->bearer())->assertOk();
        $this->assertMatchesRegularExpression('/^shelf_apple_[a-f0-9]{64}$/D', $second->json('app_user_id'));
        $this->assertNotSame($first->json('app_user_id'), $second->json('app_user_id'));
        $this->getJson('/api/purchases/config?platform=ios')->assertJsonMissing(['app_user_id' => $second->json('app_user_id')]);
        $this->webhook(['app_user_id' => $first->json('app_user_id')])->assertStatus(409);
        $this->webhook(['app_user_id' => (string) $this->reader->id])->assertStatus(409);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_expired_revoked_future_and_wrong_reader_or_book_sessions_never_record_or_unlock(): void
    {
        $base = DB::table('apple_purchase_sessions')->first();
        foreach ([['expires_at_ms' => $this->event['purchased_at_ms'] - 1],
            ['issued_at_ms' => $this->event['purchased_at_ms'] + 1],
            ['revoked_at_ms' => $this->event['purchased_at_ms']], ['collection_id' => 4],
            ['reader_id' => 99999]] as $changes) {
            DB::table('apple_purchase_sessions')->where('id', $base->id)->update($changes);
            $response = $this->webhook();
            $this->assertContains($response->status(), [403, 409]);
            DB::table('apple_purchase_sessions')->where('id', $base->id)->update((array) $base);
        }
        $this->travel(16)->minutes();
        $this->webhook()->assertStatus(409);
        $this->hideApplePurchase = true;
        $this->postJson('/api/library/confirm', [], $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('sales_ledger', 0);
        $this->getJson('/api/library/books/'.$this->book->slug, $this->bearer())->assertNotFound();
    }

    public function test_confirmation_cannot_consume_another_readers_session_or_cross_session_proof(): void
    {
        $other = Reader::create(['email' => 'other@example.test']);
        $other->forceFill(['email_verified_at' => now()])->save();
        $this->postJson('/api/library/confirm', [], $this->bearer($other))->assertOk()->assertJsonCount(0, 'books');
        $this->proof['customer_id'] = $this->proof['original_customer_id'] = 'shelf_apple_'.str_repeat('b', 64);
        $this->webhook()->assertStatus(503);
        $this->proof['customer_id'] = $this->proof['original_customer_id'] = (string) $this->reader->id;
        $this->webhook()->assertStatus(503);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertNull(DB::table('apple_purchase_sessions')->first()->transaction_id);
    }

    public function test_consumed_session_cannot_add_another_transaction_but_exact_retries_are_idempotent(): void
    {
        $this->webhook()->assertOk();
        $this->travel(16)->minutes();
        $this->webhook()->assertOk(); // Historical retries cannot mint another sale or lease.
        $this->webhook(['id' => 'retry'])->assertOk();
        $this->webhook(['id' => 'replay', 'transaction_id' => '100000000000009'])->assertStatus(409);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertSame(1, SalesLedger::withTestPurchases()->where('status', 'sale')->count());
        $this->assertSame(0, SalesLedger::count());
        $this->assertDatabaseHas('apple_purchase_sessions', ['transaction_id' => $this->event['transaction_id']]);
    }

    public function test_closed_apple_gate_allows_independent_play_library_confirm_restore_and_content(): void
    {
        $play = $this->existingPlayOwnership();
        $original = $play->fresh()->toArray(); $sale = $play->entries()->first()->toArray();
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->ownedTestConsent()->assertOk(); $this->webhook()->assertOk();
        $provider = \Mockery::mock(\App\Services\Purchases\RevenueCatClient::class);
        $provider->shouldReceive('subscriber')->times(4)->andReturn([]);
        $provider->shouldReceive('confirms')->times(4)->andReturn(true);
        $this->app->instance(\App\Services\Purchases\RevenueCatClient::class, $provider);
        foreach ([false, true] as $refunded) {
            if ($refunded) { config(['purchases.apple_sandbox_enabled' => true]); $this->refund()->assertOk(); }
            config(['purchases.apple_sandbox_enabled' => false, 'purchases.apple_owned_book3_test_enabled' => false]);
            $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonPath('books.0.id', 3)
                ->assertJsonPath('apple_sandbox_book3_confirmed', false)->assertJsonPath('apple_purchase_identity', null);
            $this->postJson('/api/library/'.($refunded ? 'restore' : 'confirm'), [], $this->bearer())->assertOk()->assertJsonPath('books.0.id', 3);
            $this->getJson('/api/library/books/'.$this->book->slug.'/content', $this->bearer())->assertOk();
            $this->getJson('/api/library/poems/'.$this->book->poems()->first()->id, $this->bearer())->assertOk();
            $this->ownedTestConsent()->assertStatus(503); $this->webhook()->assertStatus(503);
        }
        $this->assertSame($original, $play->fresh()->toArray());
        $this->assertSame($sale, $play->entries()->first()->toArray());
        $this->assertDatabaseCount('purchases', 2); $this->assertSame(0, SalesLedger::count());
    }

    public function test_closed_gate_apple_only_history_retained_but_excluded_from_library_download_and_paid_media(): void
    {
        $this->webhook()->assertOk();
        $history = Purchase::first()->toArray();
        $entitlement = (array) DB::table('book_entitlements')->first();
        config(['purchases.apple_sandbox_enabled' => false, 'purchases.apple_owned_book3_test_enabled' => false]);
        $sent = count(Http::recorded());
        foreach (['/api/library', '/api/library/confirm', '/api/library/restore'] as $path) {
            ($path === '/api/library' ? $this->getJson($path, $this->bearer()) : $this->postJson($path, [], $this->bearer()))
                ->assertOk()->assertJsonCount(0, 'books');
        }
        $poem = $this->book->poems()->first();
        foreach (['/api/library/books/'.$this->book->slug, '/api/library/books/'.$this->book->slug.'/content',
            '/api/library/books/'.$this->book->slug.'/cover', '/api/library/poems/'.$poem->id,
            '/api/library/poems/'.$poem->id.'/audio', '/api/library/poems/'.$poem->id.'/media/artwork'] as $path) {
            $this->getJson($path, $this->bearer())->assertNotFound();
        }
        $this->assertSame($sent, count(Http::recorded()));
        $this->assertSame($history, Purchase::first()->toArray());
        $this->assertSame($entitlement, (array) DB::table('book_entitlements')->first());
        $this->assertSame(0, SalesLedger::count());
    }

    public function test_session_schema_rollback_is_reversible_empty_and_refuses_evidence_loss(): void
    {
        $migration = require database_path('migrations/2026_10_15_010000_create_apple_purchase_sessions.php');
        try { $migration->down(); $this->fail('Session evidence must be preserved.'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('preservation plan', $e->getMessage()); }
        DB::table('apple_purchase_sessions')->delete();
        $migration->down();
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('apple_purchase_sessions'));
        $migration->up();
        $this->assertDatabaseCount('apple_purchase_sessions', 0);
    }

    public function test_closed_gate_cannot_use_apple_history_to_hide_failed_play_proof(): void
    {
        $play = $this->existingPlayOwnership();
        config(['purchases.apple_owned_book3_test_enabled' => true]);
        $this->ownedTestConsent()->assertOk(); $this->webhook()->assertOk();
        config(['purchases.apple_sandbox_enabled' => false, 'purchases.apple_owned_book3_test_enabled' => false]);
        $provider = \Mockery::mock(\App\Services\Purchases\RevenueCatClient::class);
        $provider->shouldReceive('subscriber')->once()->andReturn([]);
        $provider->shouldReceive('confirms')->once()->andReturn(false);
        $this->app->instance(\App\Services\Purchases\RevenueCatClient::class, $provider);
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->getJson('/api/library/books/'.$this->book->slug.'/content', $this->bearer())->assertNotFound();
        $this->assertTrue($play->entries()->where('status', 'revoke')->exists());
        $this->assertSame(0, SalesLedger::count());
        $this->assertDatabaseCount('purchases', 2);
    }

}
