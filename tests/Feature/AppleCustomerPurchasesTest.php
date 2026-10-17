<?php

namespace Tests\Feature;

use App\Models\{Author, AuthorShareAgreement, Collection, Purchase, PurchaseEvent, Reader, SalesLedger};
use App\Services\BookAccessService;
use App\Services\Purchases\{ApplePurchaseProof, AppleSandboxPurchases, PurchaseService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http};
use Tests\TestCase;

class AppleCustomerPurchasesTest extends TestCase
{
    use RefreshDatabase;
    private Reader $reader;
    private array $proofs = [];
    private array $books = [];
    private bool $providerFails = false;
    private bool $wrongEntitlement = false;
    private bool $wrongMapping = false;
    private bool $missingCustomer = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfSecond());
        config(['reader_auth.enabled' => true, 'purchases.apple_customers_enabled' => true,
            'purchases.apple_sandbox_enabled' => false, 'purchases.apple_owned_book3_test_enabled' => false,
            'purchases.production_enabled' => true, 'purchases.enabled' => false,
            'purchases.apple_public_sdk_key' => 'appl_synthetic', 'purchases.apple_app_id' => 'appAppleFixture',
            'purchases.apple_project_id' => 'projFixture', 'purchases.apple_webhook_authorization' => 'synthetic-apple-auth',
            'purchases.metadata_key' => 'synthetic', 'purchases.metadata_key_path' => null]);
        $this->reader = Reader::create(['email' => 'ordinary@example.test']);
        $this->reader->forceFill(['email_verified_at' => now()])->save();
        foreach (range(3, 8) as $id) {
            $book = new Collection(['title' => 'Synthetic book '.$id, 'status' => 'published', 'price_usd' => '2.99']);
            $book->forceFill(['id' => $id])->save();
            $this->books[$id] = $book;
        }
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            if ($this->providerFails) { return Http::response([], 503); }
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (str_contains($path, '/apps/')) {
                return Http::response(['id' => 'appAppleFixture', 'type' => 'app_store', 'project_id' => 'projFixture',
                    'app_store' => ['bundle_id' => 'services.shelf.app']]);
            }
            if (preg_match('#/products/prod([3-8])$#', $path, $m)) {
                return Http::response(['id' => 'prod'.$m[1], 'app_id' => 'appAppleFixture',
                    'store_identifier' => $this->wrongMapping ? 'unlock_all' : 'shelf_book_'.$m[1], 'type' => 'non_consumable']);
            }
            if (preg_match('#/purchases/purch([3-8])/entitlements$#', $path, $m)) {
                return Http::response($this->listing([['object' => 'entitlement', 'state' => 'active', 'project_id' => 'projFixture',
                    'lookup_key' => $this->wrongEntitlement ? 'unlock_all' : 'shelf_book_'.$m[1]]]));
            }
            if (str_contains($path, '/customers/')) {
                if ($this->missingCustomer) { return Http::response([], 404); }
                return Http::response($this->listing(array_values(array_filter($this->proofs,
                    fn ($p) => $p['environment'] === $request['environment']))));
            }
            return Http::response($this->listing(array_values(array_filter($this->proofs,
                fn ($p) => $p['store_purchase_identifier'] === $request['store_purchase_identifier']))));
        });
    }

    private function listing(array $items): array { return ['object' => 'list', 'items' => $items, 'next_page' => null]; }
    private function bearer(?Reader $r = null): array { app('auth')->forgetGuards(); return ['Authorization' => 'Bearer '.($r ?? $this->reader)->createToken('synthetic')->plainTextToken]; }
    private function consent(int $id = 3, ?Reader $reader = null) {
        return $this->postJson('/api/library/books/'.$id.'/consent', ['agree' => true, 'store' => 'APP_STORE',
            'checkout_mode' => 'production', 'purchase_session_version' => 2], $this->bearer($reader));
    }
    private function prepare(int $id = 3, string $environment = 'PRODUCTION'): array
    {
        $this->consent($id)->assertOk()->assertJsonPath('app_user_id', (string) $this->reader->id);
        $ms = now()->getTimestampMs();
        $this->proofs[$id] = ['object' => 'purchase', 'id' => 'purch'.$id, 'customer_id' => (string) $this->reader->id,
            'original_customer_id' => (string) $this->reader->id, 'product_id' => 'prod'.$id,
            'store' => 'app_store', 'environment' => strtolower($environment), 'ownership' => 'purchased', 'quantity' => 1,
            'store_purchase_identifier' => (string) (100000000000000 + $id), 'purchased_at' => $ms, 'status' => 'owned'];
        return ['id' => 'customer-sale-'.$id, 'type' => 'NON_RENEWING_PURCHASE', 'environment' => $environment,
            'store' => 'APP_STORE', 'app_id' => 'appAppleFixture', 'app_user_id' => (string) $this->reader->id,
            'product_id' => 'shelf_book_'.$id, 'transaction_id' => $this->proofs[$id]['store_purchase_identifier'],
            'purchased_at_ms' => $ms, 'event_timestamp_ms' => $ms, 'currency' => 'USD', 'price_in_purchased_currency' => '2.99'];
    }
    private function webhook(array $event) { return $this->postJson('/api/purchases/webhook', ['event' => $event], ['Authorization' => 'synthetic-apple-auth']); }

    public function test_all_six_normal_products_use_one_authenticated_customer_identity_and_real_ledger(): void
    {
        $this->getJson('/api/purchases/config?platform=ios')->assertJsonPath('customer_mode', true)
            ->assertJsonPath('production_checkout_enabled', true)->assertJsonCount(6, 'allowed_product_ids');
        foreach (range(3, 8) as $id) {
            $event = $this->prepare($id); $this->webhook($event)->assertOk(); $this->webhook($event)->assertOk();
            $this->assertTrue(app(BookAccessService::class)->ownsBook($this->reader, $this->books[$id]));
            $this->consent($id)->assertStatus(409);
        }
        $this->assertDatabaseCount('purchases', 6); $this->assertDatabaseCount('purchase_events', 6);
        $this->assertSame(6, SalesLedger::count()); $this->assertEquals(17.94, SalesLedger::sum('amount'));
        $this->assertDatabaseCount('apple_purchase_sessions', 0);
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(6, 'books');
        $other = Reader::create(['email' => 'other@example.test']); $other->forceFill(['email_verified_at' => now()])->save();
        $this->getJson('/api/library/books/'.$this->books[8]->slug, $this->bearer($other))->assertNotFound();
    }

    public function test_restore_confirms_each_book_from_rest_when_webhook_is_delayed(): void
    {
        foreach (range(3, 8) as $id) { $this->prepare($id); }
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(6, 'books');
        $this->assertDatabaseCount('purchases', 6); $this->assertSame(6, SalesLedger::count());
        $this->assertSame(0, SalesLedger::whereNotNull('amount')->count());
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(6, 'books');
        $this->assertDatabaseCount('purchases', 6);
    }

    public function test_actual_sandbox_receipt_remains_test_even_when_customer_requested_production(): void
    {
        $this->webhook($this->prepare(8, 'SANDBOX'))->assertOk();
        $this->assertSame(0, SalesLedger::count()); $this->assertSame(1, SalesLedger::withTestPurchases()->count());
        $this->assertSame('test', SalesLedger::withTestPurchases()->first()->earnings_status);
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(1, 'books');
    }

    public function test_gate_closure_preserves_existing_production_access_restore_and_refunds_without_new_sales(): void
    {
        $event = $this->prepare(4); $this->webhook($event)->assertOk();
        config(['purchases.production_enabled' => false, 'purchases.apple_customers_enabled' => false]);
        $this->assertTrue(app(BookAccessService::class)->ownsBook($this->reader, $this->books[4]));
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(1, 'books');
        $this->consent(5)->assertStatus(503);
        $this->proofs[4]['status'] = 'refunded';
        $this->webhook(array_replace($event, ['id' => 'closed-refund', 'type' => 'CANCELLATION',
            'event_timestamp_ms' => $event['event_timestamp_ms'] + 1000]))->assertOk();
        $this->assertFalse(app(BookAccessService::class)->ownsBook($this->reader, $this->books[4]));
        $this->assertSame(2, SalesLedger::count());
    }

    public function test_refund_preserves_other_store_access_and_reversals_append(): void
    {
        $event = $this->prepare(7); $this->webhook($event)->assertOk();
        $play = Purchase::create(['reader_id' => $this->reader->id, 'collection_id' => 7, 'store' => 'PLAY_STORE',
            'environment' => 'PRODUCTION', 'transaction_id' => 'play-separate', 'product_id' => 'shelf_book_7', 'purchased_at' => now()]);
        $this->proofs[7]['status'] = 'refunded';
        $this->webhook(array_replace($event, ['id' => 'refund7', 'type' => 'CANCELLATION', 'event_timestamp_ms' => $event['event_timestamp_ms'] + 1000]))->assertOk();
        $this->assertTrue(app(BookAccessService::class)->ownsBook($this->reader, $this->books[7]));
        $this->assertTrue(AppleSandboxPurchases::active($play));
        $this->proofs[7]['status'] = 'owned';
        $this->webhook(array_replace($event, ['id' => 'reverse7', 'type' => 'REFUND_REVERSED', 'event_timestamp_ms' => $event['event_timestamp_ms'] + 2000]))->assertOk();
        $this->assertSame(3, SalesLedger::count()); $this->assertEquals(2.99, SalesLedger::sum('amount'));
    }

    public function test_real_checkout_off_staging_and_blocked_or_unverified_readers_cannot_buy(): void
    {
        config(['purchases.production_enabled' => false]);
        $this->getJson('/api/purchases/config?platform=ios')->assertJsonPath('production_checkout_enabled', false);
        $this->consent()->assertStatus(503); $this->assertDatabaseCount('purchase_consents', 0);
        config(['purchases.production_enabled' => true, 'staging.testing' => true]);
        $this->assertFalse(ApplePurchaseProof::customersConfigured());
        $this->consent()->assertUnauthorized(); // Private staging stops before checkout.
        config(['staging.testing' => false]);
        $this->reader->forceFill(['buying_blocked' => true])->save(); $this->consent()->assertForbidden();
        $this->reader->forceFill(['buying_blocked' => false, 'email_verified_at' => null])->save(); $this->consent()->assertForbidden();
    }

    public function test_invalid_mapping_identity_environment_time_and_entitlement_never_unlock(): void
    {
        $event = $this->prepare(5); $valid = $this->proofs[5];
        foreach (['original_customer_id' => '999', 'customer_id' => '999', 'environment' => 'sandbox',
            'purchased_at' => $valid['purchased_at'] + 1, 'store' => 'play_store', 'ownership' => 'family_shared'] as $k => $v) {
            $this->proofs[5] = array_replace($valid, [$k => $v]); $this->webhook($event)->assertStatus(503);
        }
        $this->proofs[5] = $valid; $this->wrongMapping = true; $this->webhook($event)->assertStatus(503);
        $this->wrongMapping = false; $this->wrongEntitlement = true; $this->webhook($event)->assertStatus(503);
        $this->assertDatabaseCount('purchases', 0); $this->assertDatabaseCount('sales_ledger', 0);
    }

    public function test_refund_observed_on_restore_removes_access_and_failed_proof_does_not_renew_lease(): void
    {
        $event = $this->prepare(6); $this->webhook($event)->assertOk();
        $this->providerFails = true;
        $this->postJson('/api/library/restore', [], $this->bearer())->assertStatus(503)->assertJsonMissing(['offline_valid_until']);
        $this->providerFails = false; $this->proofs[6]['status'] = 'refunded';
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->proofs[6]['status'] = 'owned';
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(0, 'books');
    }

    public function test_each_book_keeps_its_own_purchase_time_agreement_snapshot(): void
    {
        $author = Author::create(['name' => 'Synthetic holder']);
        foreach ([3, 8] as $id) {
            $agreement = AuthorShareAgreement::create(['collection_id' => $id,
                'contributors' => [['author_id' => $author->id, 'percentage' => $id === 3 ? 100 : 50]],
                'basis' => 'net', 'deductions' => 'Store fees', 'sharing_terms' => 'Synthetic book '.$id,
                'starts_at' => now()->subDay()]);
            $this->webhook($this->prepare($id))->assertOk();
            $sale = SalesLedger::whereHas('purchase', fn ($q) => $q->where('collection_id', $id))->first();
            $this->assertSame($agreement->id, $sale->agreement_snapshot['id']);
            $this->assertSame($id, $sale->agreement_snapshot['collection_id']);
            $this->assertNull($sale->estimated_earnings);
            $this->assertSame('unknown', $sale->earnings_status);
        }
    }

    public function test_cross_platform_owned_books_cannot_start_a_second_checkout_but_inflight_proof_is_retained(): void
    {
        $event = $this->prepare(3); // Consent precedes independent Play confirmation.
        Purchase::create(['reader_id' => $this->reader->id, 'collection_id' => 3, 'store' => 'PLAY_STORE',
            'environment' => 'PRODUCTION', 'transaction_id' => 'play-existing', 'product_id' => 'shelf_book_3', 'purchased_at' => now()]);
        app(PurchaseService::class)->refreshAccess($this->reader, $this->books[3]);
        $this->consent(3)->assertStatus(409);
        $this->webhook($event)->assertOk();
        $this->assertDatabaseCount('purchases', 2);
        $this->proofs[3]['status'] = 'refunded';
        $this->webhook(array_replace($event, ['id' => 'refund-inflight', 'type' => 'CANCELLATION',
            'event_timestamp_ms' => $event['event_timestamp_ms'] + 1000]))->assertOk();
        $this->assertTrue(app(BookAccessService::class)->ownsBook($this->reader, $this->books[3]));
    }

    public function test_new_customer_with_no_revenuecat_history_has_an_empty_library(): void
    {
        $this->missingCustomer = true;
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books');
    }
}
