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
            'purchases.secret_key_path' => null,
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

    private function realProvider(): void
    {
        $this->event['environment'] = 'PRODUCTION';
        $this->subscriber['non_subscriptions'][$this->book->product_id][0]['is_sandbox'] = false;
        $this->mockProvider();
    }

    public function test_real_purchase_gate_defaults_off_and_client_flags_cannot_enable_it(): void
    {
        $this->assertFalse(\App\Services\Purchases\RevenueCatClient::productionEnabled());
        $this->realProvider();
        $this->webhook(['production_enabled' => true, 'test_mode' => false])->assertUnprocessable();
        $this->getJson('/api/purchases/config?production_enabled=true')->assertJsonPath('test_mode', true);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('sales_ledger', 0);
        Http::assertNothingSent();
    }

    public function test_verified_real_sale_and_test_sale_are_separate_idempotent_and_snapshot_preserving(): void
    {
        config(['purchases.production_enabled' => true]);
        $author = Author::create(['name' => 'Synthetic rights holder']);
        $agreement = AuthorShareAgreement::create(['collection_id' => $this->book->id,
            'contributors' => [['author_id' => $author->id, 'percentage' => 100]], 'basis' => 'net',
            'deductions' => 'Actual store deductions', 'sharing_terms' => 'Synthetic agreement', 'starts_at' => now()->subHour()]);
        // A production-enabled server still classifies verified sandbox purchases as Test.
        $this->webhook()->assertOk();
        $this->assertSame(0, SalesLedger::count());
        $this->realProvider();
        $this->event['transaction_id'] = 'GPA.real-synthetic';
        $this->event['id'] = 'real-event';
        $this->event['currency'] = 'EUR';
        $this->event['price_in_purchased_currency'] = '3.49';
        $this->webhook()->assertOk();
        $this->webhook()->assertOk();
        $this->webhook(['id' => 'real-duplicate'])->assertOk();
        $this->assertDatabaseCount('purchases', 2);
        $this->assertDatabaseCount('sales_ledger', 2);
        $sale = SalesLedger::firstOrFail();
        $this->assertSame('PRODUCTION', $sale->purchase->environment);
        $this->assertSame('Real', $sale->income_mode);
        $this->assertSame('3.490000', $sale->amount);
        $this->assertSame('EUR', $sale->currency);
        $this->assertEquals($agreement->id, $sale->agreement_snapshot['id']);
        foreach (['fees_status', 'taxes_status', 'earnings_status'] as $field) { $this->assertSame('unknown', $sale->$field); }
        foreach (['estimated_earnings', 'confirmed_owed', 'payments_made'] as $field) { $this->assertNull($sale->$field); }
        $this->assertSame('test', SalesLedger::withTestPurchases()->whereHas('purchase', fn ($q) => $q->where('environment', 'SANDBOX'))->first()->earnings_status);
        // Later disablement blocks buying, while prior real purchases still restore.
        config(['purchases.production_enabled' => false]);
        $sandbox = Purchase::where('environment', 'SANDBOX')->first();
        // Retire the Test purchase; restore must check the remaining real environment.
        $this->webhook(['id' => 'test-refund', 'type' => 'CANCELLATION', 'environment' => 'SANDBOX', 'transaction_id' => $sandbox->transaction_id])->assertOk();
        $this->postJson('/api/library/restore', [], $this->bearer())->assertOk()->assertJsonPath('books.0.id', $this->book->id);
        $before = $sale->getAttributes();
        $this->webhook(['id' => 'real-refund', 'type' => 'CANCELLATION'])->assertOk();
        $this->webhook(['id' => 'real-refund-again', 'type' => 'CANCELLATION'])->assertOk();
        $this->assertSame($before, $sale->fresh()->getAttributes());
        $this->assertSame(2, SalesLedger::count());
        $this->assertSame('-3.490000', SalesLedger::where('status', 'refund')->first()->amount);
        $this->assertDatabaseHas('book_entitlements', ['active' => false]);
    }

    public function test_missing_malformed_conflicting_or_ambiguous_provider_environment_never_unlocks(): void
    {
        config(['purchases.production_enabled' => true]);
        $this->event['environment'] = 'PRODUCTION';
        foreach ([null, 'false', 0, true] as $flag) {
            $this->subscriber['non_subscriptions'][$this->book->product_id][0]['is_sandbox'] = $flag;
            $this->mockProvider();
            $this->webhook()->assertStatus(503);
        }
        unset($this->subscriber['non_subscriptions'][$this->book->product_id][0]['is_sandbox']);
        $this->mockProvider(); $this->webhook()->assertStatus(503);
        $this->realProvider();
        $validDate = $this->subscriber['non_subscriptions'][$this->book->product_id][0]['purchase_date'];
        foreach (['', 'now', 'invalid-date'] as $date) {
            $this->subscriber['non_subscriptions'][$this->book->product_id][0]['purchase_date'] = $date;
            $this->mockProvider(); $this->webhook()->assertStatus(503);
        }
        $this->subscriber['non_subscriptions'][$this->book->product_id][0]['purchase_date'] = $validDate;
        $this->realProvider();
        $this->subscriber['non_subscriptions'][$this->book->product_id][] = $this->subscriber['non_subscriptions'][$this->book->product_id][0];
        $this->mockProvider(); $this->webhook()->assertStatus(503);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('sales_ledger', 0);
        $this->assertDatabaseCount('book_entitlements', 0);
    }

    public function test_real_provider_outage_refund_and_wrong_reader_or_product_fail_closed(): void
    {
        config(['purchases.production_enabled' => true]); $this->realProvider();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.revenuecat.com/*' => Http::response([], 503)]);
        $this->webhook()->assertStatus(503);
        $this->subscriber['original_app_user_id'] = '999'; $this->mockProvider(); $this->webhook()->assertStatus(503);
        $this->subscriber['original_app_user_id'] = (string) $this->reader->id;
        $this->subscriber['entitlements'][$this->book->product_id]['product_identifier'] = 'shelf_book_999';
        $this->mockProvider(); $this->webhook()->assertStatus(503);
        $this->subscriber['entitlements'][$this->book->product_id]['product_identifier'] = $this->book->product_id;
        $this->subscriber['non_subscriptions'][$this->book->product_id][0]['refunded_at'] = now()->toIso8601String();
        $this->mockProvider(); $this->webhook()->assertStatus(503);
        $this->assertDatabaseCount('purchases', 0); $this->assertDatabaseCount('book_entitlements', 0);
    }

    public function test_real_transaction_cannot_move_reader_book_or_environment_and_reordered_refund_stays_locked(): void
    {
        config(['purchases.production_enabled' => true]); $this->realProvider();
        $this->webhook(['id' => 'real-refund-before-sale', 'type' => 'CANCELLATION'])->assertOk();
        $this->webhook()->assertOk();
        $this->assertDatabaseHas('book_entitlements', ['active' => false]);
        $other = Reader::create(['email' => 'other-real@example.test']);
        $this->webhook(['id' => 'move-reader', 'app_user_id' => (string) $other->id])->assertConflict();
        $otherBook = Collection::create(['title' => 'Other synthetic book', 'status' => 'published', 'price_usd' => '2.99']);
        $this->webhook(['id' => 'move-book', 'product_id' => $otherBook->product_id])->assertConflict();
        $this->webhook(['id' => 'relabel-as-test', 'environment' => 'SANDBOX'])->assertConflict();
        $this->webhook(['transaction_id' => 'GPA.other-synthetic'])->assertConflict();
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('sales_ledger', 2);
    }

    public function test_unknown_reverification_does_not_mutate_real_history_or_renew_offline_lease(): void
    {
        config(['purchases.production_enabled' => true]); $this->realProvider(); $this->webhook()->assertOk();
        $before = DB::table('book_entitlements')->first();
        $this->subscriber['non_subscriptions'][$this->book->product_id][0]['is_sandbox'] = 'false';
        $this->mockProvider();
        $this->getJson('/api/library', $this->bearer())->assertStatus(503)->assertJsonMissingPath('offline_valid_until');
        $this->assertEquals($before, DB::table('book_entitlements')->first());
        $this->assertDatabaseCount('sales_ledger', 1);
        $this->assertDatabaseHas('book_entitlements', ['active' => true]);
    }

    public function test_real_refund_after_account_deletion_remains_processable_with_sales_off(): void
    {
        config(['purchases.production_enabled' => true]); $this->realProvider(); $this->webhook()->assertOk();
        app(AccountActions::class)->delete($this->reader);
        config(['purchases.production_enabled' => false]);
        $this->webhook(['id' => 'deleted-real-refund', 'type' => 'CANCELLATION'])->assertOk();
        $this->assertDatabaseCount('purchases', 1); $this->assertDatabaseCount('sales_ledger', 2);
        $this->assertSame(2, SalesLedger::count());
        $this->assertDatabaseCount('book_entitlements', 0);
    }

    public function test_real_entitlement_direct_requests_and_confirmed_provider_refund(): void
    {
        config(['purchases.production_enabled' => true]); $this->realProvider();
        $item = $this->book->poems()->first();
        $path = '/api/library/poems/'.$item->id;
        $this->getJson($path, $this->bearer())->assertNotFound();
        $this->webhook()->assertOk();
        $this->getJson($path, $this->bearer())->assertOk()->assertSee('Paid text');
        $other = Reader::create(['email' => 'other-direct-real@example.test']);
        $other->forceFill(['email_verified_at' => now()])->save();
        $this->getJson($path, $this->bearer($other))->assertNotFound();
        $this->subscriber['entitlements'] = [];
        $this->subscriber['non_subscriptions'][$this->book->product_id][0]['refunded_at'] = now()->toIso8601String();
        $this->mockProvider();
        $this->getJson('/api/library', $this->bearer())->assertOk()->assertJsonCount(0, 'books');
        $this->getJson($path, $this->bearer())->assertNotFound();
        $this->assertDatabaseHas('sales_ledger', ['status' => 'revoke']);
        $this->assertDatabaseHas('book_entitlements', ['active' => false]);
    }

    public function test_staging_can_never_accept_real_sales_even_when_gate_is_forced_on(): void
    {
        config(['staging.testing' => true, 'purchases.production_enabled' => true]);
        $this->assertFalse(\App\Services\Purchases\RevenueCatClient::productionEnabled());
        $this->realProvider();
        try { app(PurchaseService::class)->receive(array_replace($this->event, ['app_user_id' => 'staging_'.$this->reader->id]));
            $this->fail('Staging accepted a real sale');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(422, $error->getStatusCode()); }
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_private_credential_path_fails_closed_without_using_inline_fallback(): void
    {
        config(['purchases.secret_key_path' => base_path('.env')]);
        $this->getJson('/api/purchases/config')->assertOk()->assertJson(['enabled' => false, 'public_sdk_key' => null]);
        $this->webhook()->assertStatus(503);
        Http::assertNothingSent();
        $this->assertDatabaseCount('purchases', 0);
        config(['purchases.secret_key_path' => '/home/shelf/secrets/missing-synthetic-verifier.txt']);
        $this->getJson('/api/purchases/config')->assertJson(['enabled' => false]);
    }
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
        $sale = SalesLedger::withTestPurchases()->firstOrFail();
        $this->assertEquals($agreement->id, $sale->agreement_snapshot['id']);
        $this->assertNull($sale->estimated_earnings);
        $this->assertSame('test', $sale->earnings_status);
        $this->assertSame(0, SalesLedger::count());
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
