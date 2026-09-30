<?php

namespace Tests\Feature;

use App\Models\{Author, AuthorShareAgreement, Collection, Purchase, Reader, SalesLedger, User};
use App\Services\Accounts\AccountActions;
use App\Services\Purchases\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http, Storage};
use Tests\TestCase;

class BookPurchasesTest extends TestCase
{
    use RefreshDatabase;
    private Reader $reader;
    private Collection $book;
    private array $event;
    private array $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        config(['reader_auth.enabled' => true, 'purchases.enabled' => true, 'purchases.secret_key' => 'synthetic-secret',
            'purchases.webhook_authorization' => 'synthetic-auth', 'purchases.app_id' => 'shelf-test-app', 'purchases.public_sdk_key' => 'goog_synthetic']);
        Http::preventStrayRequests();
        $this->reader = Reader::create(['email' => 'synthetic@example.test']);
        $this->reader->forceFill(['email_verified_at' => now()])->save();
        config(['purchases.test_reader_ids' => [$this->reader->id]]);
        $this->book = Collection::create(['title' => 'Synthetic book', 'status' => 'published', 'price_usd' => '2.99']);
        $this->book->poems()->create(['body' => "Sample\nPaid text", 'excerpt' => '', 'is_active' => true, 'sample_mode' => 'partial', 'sample_unit' => 'lines', 'sample_count' => 1,
            'audio_path' => 'synthetic.mp3', 'artwork_path' => 'synthetic.png']);
        DB::table('purchase_consents')->insert(['reader_id' => $this->reader->id, 'collection_id' => $this->book->id, 'wording' => PurchaseService::CONSENT, 'created_at' => now()->subMinute()]);
        $this->event = ['id' => 'event-1', 'type' => 'NON_RENEWING_PURCHASE', 'environment' => 'SANDBOX', 'store' => 'PLAY_STORE',
            'app_id' => 'shelf-test-app', 'app_user_id' => (string) $this->reader->id, 'product_id' => $this->book->product_id,
            'transaction_id' => 'GPA.synthetic', 'purchased_at_ms' => now()->getTimestampMs(), 'event_timestamp_ms' => now()->getTimestampMs(),
            'currency' => 'USD', 'price_in_purchased_currency' => '2.99'];
        $this->subscriber = ['original_app_user_id' => (string) $this->reader->id,
            'entitlements' => [$this->book->product_id => ['product_identifier' => $this->book->product_id, 'expires_date' => null]],
            'non_subscriptions' => [$this->book->product_id => [['id' => 'rc-internal-id', 'store' => 'play_store', 'is_sandbox' => true, 'purchase_date' => now()->toIso8601String()]]]];
        $this->mockProvider();
    }

    private function mockProvider(): void { Http::swap(new \Illuminate\Http\Client\Factory); Http::preventStrayRequests(); Http::fake(['api.revenuecat.com/*' => Http::response(['subscriber' => $this->subscriber])]); }
    private function webhook(array $changes = []) { return $this->postJson('/api/purchases/webhook', ['event' => array_replace($this->event, $changes)], ['Authorization' => 'synthetic-auth']); }
    private function bearer(?Reader $reader = null): array
    {
        app('auth')->forgetGuards();
        return ['Authorization' => 'Bearer '.($reader ?? $this->reader)->createToken('synthetic', ['reader'], now()->addHour())->plainTextToken];
    }

    public function test_authenticated_webhook_and_rest_both_required_idempotent_and_no_global_or_real_money(): void
    {
        $this->postJson('/api/purchases/webhook', ['event' => $this->event])->assertUnauthorized();
        foreach ([['environment' => 'PRODUCTION'], ['app_id' => 'other-app'], ['store' => 'APP_STORE'], ['type' => 'TEMPORARY_ENTITLEMENT_GRANT']] as $change) {
            $this->webhook($change)->assertUnprocessable();
        }
        $this->webhook(['product_id' => 'pitswal_unlock_all_v1'])->assertNotFound();
        $this->subscriber['entitlements'] = ['unlock_all' => ['product_identifier' => 'pitswal_unlock_all_v1', 'expires_date' => null]];
        $this->mockProvider();
        $this->webhook()->assertStatus(503);
        $this->assertDatabaseCount('purchases', 0);
        $this->subscriber['entitlements'] = [$this->book->product_id => ['product_identifier' => $this->book->product_id, 'expires_date' => null]];
        $this->mockProvider();
        $this->webhook()->assertOk();
        $this->webhook()->assertOk();
        $this->webhook(['id' => 'duplicate-transaction-event'])->assertOk();
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseHas('book_entitlements', ['reader_id' => $this->reader->id, 'collection_id' => $this->book->id, 'active' => true]);
    }

    public function test_direct_requests_other_accounts_refund_old_media_and_withdrawn_library(): void
    {
        Storage::fake('audio'); Storage::fake('artwork');
        Storage::disk('audio')->put('synthetic.mp3', 'audio'); Storage::disk('artwork')->put('synthetic.png', 'artwork');
        $item = $this->book->poems()->first();
        $this->getJson('/api/poems/'.$item->id)->assertDontSee('Paid text');
        $this->webhook()->assertOk();
        $other = Reader::create(['email' => 'other@example.test']);
        $other->forceFill(['email_verified_at' => now()])->save();
        config(['purchases.test_reader_ids' => [$this->reader->id, $other->id]]);
        $paths = ['/api/library/books/'.$this->book->slug, '/api/library/books/'.$this->book->slug.'/content',
            '/api/library/poems/'.$item->id, '/api/library/poems/'.$item->id.'/audio',
            '/api/library/poems/'.$item->id.'/media/audio', '/api/library/poems/'.$item->id.'/media/artwork'];
        foreach ($paths as $path) {
            app('auth')->forgetGuards();
            $this->getJson($path)->assertUnauthorized();
            $this->getJson($path, $this->bearer($other))->assertNotFound();
            $this->getJson($path, $this->bearer())->assertOk();
        }
        $this->book->changeStatus('withdrawn');
        $this->getJson('/api/collections/'.$this->book->slug)->assertNotFound();
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonPath('books.0.id', $this->book->id);
        $this->postJson('/api/library/books/'.$this->book->id.'/consent', ['agree' => true], $this->bearer($other))->assertConflict();
        $this->webhook(['id' => 'refund-1', 'type' => 'CANCELLATION'])->assertOk();
        $this->webhook(['id' => 'refund-duplicate', 'type' => 'CANCELLATION'])->assertOk();
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('sales_ledger', 2);
        $this->assertDatabaseHas('sales_ledger', ['status' => 'refund', 'amount' => '-2.990000']);
        foreach ($paths as $path) { $this->getJson($path, $this->bearer())->assertNotFound(); }
        $this->postJson('/api/library/restore', [], $this->bearer())->assertJsonCount(0, 'books');
        $this->get('/admin', $this->bearer())->assertRedirect('/admin/login');
    }

    public function test_revoke_before_purchase_blocks_reordered_sale_and_restore_does_not_invent_ownership(): void
    {
        $this->webhook(['id' => 'revoke-first', 'type' => 'EXPIRATION'])->assertOk();
        $this->webhook()->assertOk();
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->assertDatabaseHas('book_entitlements', ['active' => false]);
        $this->assertDatabaseCount('sales_ledger', 2);
    }

    public function test_periodic_rest_check_revokes_missing_purchase_and_outage_never_renews_offline_lease(): void
    {
        $this->webhook()->assertOk();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.revenuecat.com/*' => Http::response([], 503)]);
        $this->getJson('/api/library', $this->bearer())->assertStatus(503)->assertJsonMissingPath('offline_valid_until');
        $this->subscriber['entitlements'] = [];
        $this->mockProvider();
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->assertDatabaseHas('sales_ledger', ['status' => 'revoke']);
    }

    public function test_buying_requires_verified_unblocked_reader_agreement_and_available_book(): void
    {
        $this->postJson('/api/library/books/'.$this->book->id.'/consent', [], $this->bearer())->assertUnprocessable();
        $this->postJson('/api/library/books/'.$this->book->id.'/consent', ['agree' => true], $this->bearer())->assertOk()->assertJsonPath('app_user_id', (string) $this->reader->id);
        $this->reader->forceFill(['buying_blocked' => true])->save();
        $this->postJson('/api/library/books/'.$this->book->id.'/consent', ['agree' => true], $this->bearer())->assertForbidden();
        $this->reader->forceFill(['buying_blocked' => false, 'email_verified_at' => null])->save();
        $this->postJson('/api/library/books/'.$this->book->id.'/consent', ['agree' => true], $this->bearer())->assertForbidden();
    }

    public function test_historical_agreement_and_money_records_never_change_and_account_deletion_keeps_history(): void
    {
        $author = Author::create(['name' => 'Synthetic author']);
        $agreement = AuthorShareAgreement::create(['collection_id' => $this->book->id, 'contributors' => [['author_id' => $author->id, 'percentage' => 25]],
            'basis' => 'gross', 'deductions' => 'none', 'sharing_terms' => 'Agreed share', 'starts_at' => now()->subHour()]);
        $this->webhook()->assertOk();
        $sale = SalesLedger::firstOrFail();
        $this->assertEquals($agreement->id, $sale->agreement_snapshot['id']);
        $this->assertEquals('0.747500', $sale->estimated_earnings[0]['amount']);
        AuthorShareAgreement::create(['collection_id' => $this->book->id, 'contributors' => [['author_id' => $author->id, 'percentage' => 50]],
            'basis' => 'net', 'deductions' => 'store fees', 'sharing_terms' => 'New agreement', 'starts_at' => now()->addHour()]);
        $this->assertEquals($agreement->id, $sale->fresh()->agreement_snapshot['id']);
        foreach ([fn () => $sale->update(['amount' => 999]), fn () => Purchase::first()->delete(), fn () => $agreement->delete()] as $action) {
            try { $action(); $this->fail('Rewrote history'); } catch (\LogicException) { $this->assertTrue(true); }
        }
        app(AccountActions::class)->delete($this->reader);
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseCount('book_entitlements', 0);
        $this->webhook(['id' => 'after-deletion-refund', 'type' => 'CANCELLATION'])->assertOk();
        $this->assertDatabaseCount('sales_ledger', 2);
    }

    public function test_stable_products_price_publish_validation_and_reversible_migration(): void
    {
        $id = $this->book->product_id;
        $this->book->update(['title' => 'New title']);
        $this->assertSame($id, $this->book->fresh()->product_id);
        try { $this->book->update(['product_id' => 'changed']); $this->fail('Changed stable product'); }
        catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
        $this->book->refresh()->update(['price_usd' => null]);
        try { $this->book->assertPublishable(); $this->fail('Published without price'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('price_usd', $e->errors()); }
        $migration = require database_path('migrations/2026_09_30_010000_create_book_purchases.php');
        $migration->down(); $migration->up();
        $this->assertDatabaseHas('collections', ['id' => $this->book->id, 'product_id' => $id]);
    }

    public function test_live_simulation_command_is_isolated_and_cleans_its_rows(): void
    {
        $exit = \Illuminate\Support\Facades\Artisan::call('shelf:check-purchases', ['--simulate-after-backup' => true]);
        $this->assertSame(0, $exit, \Illuminate\Support\Facades\Artisan::output());
        $this->assertDatabaseCount('readers', 1);
        $this->assertDatabaseCount('collections', 1);
        $this->assertDatabaseCount('purchases', 0);
    }
}
