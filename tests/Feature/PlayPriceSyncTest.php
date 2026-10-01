<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\CollectionResource;
use App\Jobs\SyncPlayBook;
use App\Models\BookPriceChange;
use App\Models\Collection;
use App\Models\PlayProductSync;
use App\Models\User;
use App\Services\Play\PlayPriceSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlayPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    private string $credentials;

    private string $publicKey;

    private array $products = [];

    private array $calls = [];

    private ?int $errorStatus = null;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Http::preventStrayRequests();
        config(['play_sync.enabled' => false]);
        $key = openssl_pkey_new(['private_key_bits' => 2048]);
        openssl_pkey_export($key, $private);
        $this->publicKey = openssl_pkey_get_details($key)['key'];
        $this->credentials = env('SHELF_TEST_TMP').'/play-synthetic-'.bin2hex(random_bytes(8)).'.json';
        file_put_contents($this->credentials, json_encode(['type' => 'service_account',
            'client_email' => 'synthetic@shelf-test.iam.gserviceaccount.com', 'private_key' => $private]));
        chmod($this->credentials, 0600);
        config(['play_sync.credentials_path' => $this->credentials]);
        Http::fake(function ($request) {
            $this->calls[] = ['method' => $request->method(), 'url' => $request->url(), 'body' => $request->data()];
            if (str_starts_with($request->url(), 'https://oauth2.googleapis.com/')) {
                return Http::response(['access_token' => 'synthetic-access-only', 'expires_in' => 3600]);
            }
            if ($this->errorStatus) {
                return Http::response(['error' => 'synthetic-secret-never-store'], $this->errorStatus);
            }
            if (str_contains($request->url(), 'pricing:convertRegionPrices')) {
                return Http::response(['regionVersion' => ['version' => '2026/01'],
                    'convertedRegionPrices' => ['US' => ['price' => $request->data()['price']],
                        'DE' => ['price' => ['currencyCode' => 'EUR', 'units' => '2', 'nanos' => 790000000]]],
                    'convertedOtherRegionsPrice' => ['usdPrice' => $request->data()['price'],
                        'eurPrice' => ['currencyCode' => 'EUR', 'units' => '2', 'nanos' => 790000000]]]);
            }
            preg_match('~/(?:onetimeproducts|oneTimeProducts)/(shelf_book_[0-9]+)~', $request->url(), $match);
            $id = $match[1];
            if ($request->method() === 'GET') {
                if ($request->body() !== '' || ! str_contains($request->url(), '/oneTimeProducts/')) {
                    return Http::response([], 400);
                }
                return isset($this->products[$id]) ? Http::response($this->products[$id]) : Http::response([], 404);
            }
            if ($request->method() === 'PATCH') {
                $body = $request->data();
                $body['purchaseOptions'][0]['state'] = $this->products[$id]['purchaseOptions'][0]['state'] ?? 'DRAFT';
                $this->products[$id] = $body;

                return Http::response($body);
            }
            $active = isset($request->data()['requests'][0]['activatePurchaseOptionRequest']);
            $this->products[$id]['purchaseOptions'][0]['state'] = $active ? 'ACTIVE' : 'INACTIVE';

            return Http::response(['oneTimeProducts' => [$this->products[$id]]]);
        });
    }

    protected function tearDown(): void
    {
        unlink($this->credentials);
        parent::tearDown();
    }

    private function book(string $status = 'draft'): Collection
    {
        return Collection::create(['title' => 'کتاب Exact source', 'price_usd' => '2.99', 'status' => $status]);
    }

    private function executeSync(Collection $book): PlayProductSync
    {
        config(['play_sync.enabled' => true]);
        (new SyncPlayBook($book->id))->handle(app(PlayPriceSync::class));

        return PlayProductSync::where('collection_id', $book->id)->firstOrFail();
    }

    public function test_disabled_sync_keeps_pending_without_http_or_jobs(): void
    {
        $book = $this->book();
        $this->artisan('shelf:sync-play-prices')->expectsOutputToContain('Disabled')->assertSuccessful();
        (new SyncPlayBook($book->id))->handle(app(PlayPriceSync::class));
        $this->assertSame('pending', $book->playSync->status);
        Http::assertNothingSent();
        Bus::assertNothingDispatched();
    }

    public function test_one_shot_command_stops_after_first_denial_without_touching_later_books(): void
    {
        $first = $this->book('published');
        $second = $this->book('published');
        config(['play_sync.enabled' => true]);
        $this->errorStatus = 403;
        $this->artisan('shelf:sync-play-prices', ['--once' => true])
            ->expectsOutputToContain('Stopped at the first')->assertFailed();
        $this->assertSame('error', $first->playSync()->first()->status);
        $this->assertSame(0, $second->playSync()->first()->attempts);
        $storeCalls = collect($this->calls)->filter(fn ($call) => str_contains($call['url'], 'androidpublisher.googleapis.com'));
        $this->assertCount(1, $storeCalls);
        $this->assertStringContainsString($first->product_id, $storeCalls->first()['url']);
    }

    public function test_create_local_prices_activate_and_duplicate_job_idempotent(): void
    {
        $book = $this->book('published');
        $state = $this->executeSync($book);
        $this->assertSame('synced', $state->status);
        $this->assertSame('2.99', $state->synced_price_usd);
        $this->assertTrue($state->synced_active);
        $patch = collect($this->calls)->firstWhere('method', 'PATCH');
        $this->assertStringContainsString('allowMissing=true', $patch['url']);
        $this->assertStringContainsString('regionsVersion.version=2026%2F01', $patch['url']);
        $option = $patch['body']['purchaseOptions'][0];
        $this->assertSame('buy', $option['purchaseOptionId']);
        $this->assertTrue($option['buyOption']['legacyCompatible']);
        $this->assertFalse($option['buyOption']['multiQuantityEnabled']);
        $this->assertSame('EUR', $option['regionalPricingAndAvailabilityConfigs'][1]['price']['currencyCode']);
        $conversion = collect($this->calls)->first(fn ($c) => str_contains($c['url'], 'pricing:convertRegionPrices'));
        $this->assertSame(['currencyCode' => 'USD', 'units' => '2', 'nanos' => 990000000], $conversion['body']['price']);
        $jwt = explode('.', $this->calls[0]['body']['assertion']);
        $decode = fn ($part) => base64_decode(strtr($part, '-_', '+/'));
        $this->assertSame(1, openssl_verify($jwt[0].'.'.$jwt[1], $decode($jwt[2]), $this->publicKey, OPENSSL_ALGO_SHA256));
        $claim = json_decode($decode($jwt[1]), true);
        $this->assertSame('https://www.googleapis.com/auth/androidpublisher', $claim['scope']);
        $count = count($this->calls);
        $this->executeSync($book);
        $this->assertCount($count, $this->calls);
    }

    public function test_latest_admin_price_wins_old_jobs_and_withdraw_bin_restore_sync(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        $this->actingAs($owner);
        $book = $this->book('published');
        $this->executeSync($book);
        $book->update(['price_usd' => '4.99']);
        $book->update(['price_usd' => '3.49']);
        $state = $this->executeSync($book);
        $this->assertSame('3.49', $state->synced_price_usd);
        $audit = BookPriceChange::where('collection_id', $book->id)->latest('id')->first();
        $this->assertSame($owner->id, $audit->changed_by);
        $this->assertSame('4.99', $audit->old_price_usd);
        $this->assertSame('3.49', $audit->new_price_usd);
        $this->assertNotNull($audit->created_at);
        $book->update(['status' => 'withdrawn']);
        $this->assertFalse($this->executeSync($book)->synced_active);
        $this->assertSame('INACTIVE', $this->products[$book->product_id]['purchaseOptions'][0]['state']);
        $book->update(['status' => 'published']);
        $this->assertTrue($this->executeSync($book)->synced_active);
        $book->delete();
        $this->assertFalse($this->executeSync($book)->synced_active);
        $book->restore();
        $this->assertTrue($this->executeSync($book)->synced_active);
        $this->assertSame('کتاب Exact source', $book->refresh()->title);
    }

    public function test_errors_are_redacted_back_off_and_recover_without_losing_price(): void
    {
        $book = $this->book();
        $this->errorStatus = 403;
        $state = $this->executeSync($book);
        $this->assertSame('error', $state->status);
        $this->assertStringContainsString('permissions', $state->message);
        $this->assertStringNotContainsString('synthetic-secret', json_encode($state->toArray()));
        $this->assertTrue($state->next_attempt_at->isFuture());
        $count = count($this->calls);
        $this->executeSync($book);
        $this->assertCount($count, $this->calls);
        $this->travel(61)->seconds();
        $this->errorStatus = null;
        $this->assertSame('synced', $this->executeSync($book)->status);
        $this->assertSame('2.99', $book->refresh()->price_usd);
        $book->update(['price_usd' => '3.99']);
        $this->errorStatus = 429;
        $this->assertSame('error', $this->executeSync($book)->status);
        $this->assertSame('3.99', $book->refresh()->price_usd);
    }

    public function test_no_price_deactivates_existing_product_and_credentials_are_private(): void
    {
        $book = $this->book('published');
        $this->executeSync($book);
        $book->update(['price_usd' => null, 'status' => 'draft']);
        $this->assertFalse($this->executeSync($book)->synced_active);
        $book->update(['price_usd' => '2.99']);
        chmod($this->credentials, 0644);
        clearstatcache();
        $state = $this->executeSync($book);
        $this->assertSame('error', $state->status);
        $this->assertStringContainsString('private server file', $state->message);
        $this->assertSame('2.99', $book->refresh()->price_usd);
    }

    public function test_command_queues_once_and_never_accepts_a_price_and_admin_status_renders(): void
    {
        $book = $this->book();
        config(['play_sync.enabled' => true]);
        $this->artisan('shelf:sync-play-prices', ['book' => (string) $book->id, '--pending' => true])->assertSuccessful();
        $this->artisan('shelf:sync-play-prices', ['book' => (string) $book->id, '--pending' => true])->assertSuccessful();
        Bus::assertDispatchedTimes(SyncPlayBook::class, 1);
        Bus::assertDispatched(SyncPlayBook::class, fn ($job) => $job->connection === 'database' && $job->queue === 'play-prices');
        $this->assertSame('2.99', $book->refresh()->price_usd);
        $this->actingAs(User::factory()->state(['is_owner' => true])->create());
        $this->get(CollectionResource::getUrl('index'))->assertOk()->assertSee('Google Play')->assertSee('Pending');
        $this->get(CollectionResource::getUrl('edit', ['record' => $book]))->assertOk()->assertSee('Google Play sync');
    }

    public function test_price_migration_restores_old_prices_and_keeps_traceable_reverse_entries(): void
    {
        $owner = User::factory()->state(['is_owner' => true])->create();
        foreach ([3, 4, 5, 6, 7, 8] as $id) {
            DB::table('collections')->insert(['id' => $id, 'title' => 'Synthetic '.$id,
                'slug' => 'synthetic-'.$id, 'status' => 'draft', 'price_usd' => null,
                'product_id' => 'shelf_book_'.$id, 'created_at' => now(), 'updated_at' => now()]);
        }
        $migration = require base_path('database/migrations/2026_09_30_020100_set_owner_approved_book_test_prices.php');
        $migration->up();
        $this->assertSame(6, DB::table('collections')->where('price_usd', '2.99')->count());
        $this->assertSame(6, BookPriceChange::where('changed_by', $owner->id)->count());
        $migration->down();
        $this->assertSame(6, DB::table('collections')->whereNull('price_usd')->count());
        $this->assertSame(12, BookPriceChange::count());
    }

    public function test_unexpected_options_are_not_overwritten_and_interrupted_queue_is_recovered(): void
    {
        $book = $this->book();
        $this->products[$book->product_id] = ['productId' => $book->product_id,
            'purchaseOptions' => [['purchaseOptionId' => 'unexpected-rental', 'rentOption' => []]]];
        $state = $this->executeSync($book);
        $this->assertSame('error', $state->status);
        $this->assertStringContainsString('unexpected purchase options', $state->message);
        $this->assertNull(collect($this->calls)->firstWhere('method', 'PATCH'));
        $state->update(['next_attempt_at' => null, 'queued_at' => now()->subMinutes(6)]);
        $this->assertTrue(app(PlayPriceSync::class)->enqueue($book->id));
        Bus::assertDispatchedTimes(SyncPlayBook::class, 1);
        $this->assertFalse(app(PlayPriceSync::class)->enqueue($book->id));
    }

    public function test_rolled_back_admin_save_leaves_no_price_or_pending_work(): void
    {
        $book = $this->book();
        $revision = $book->playSync->revision;
        $auditCount = BookPriceChange::count();
        config(['play_sync.enabled' => true]);
        DB::beginTransaction();
        $book->update(['price_usd' => '7.99']);
        DB::rollBack();
        $this->assertSame('2.99', $book->refresh()->price_usd);
        $this->assertSame($revision, $book->playSync()->first()->revision);
        $this->assertSame($auditCount, BookPriceChange::count());
        Bus::assertNothingDispatched();
    }
}
