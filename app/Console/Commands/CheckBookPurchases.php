<?php

namespace App\Console\Commands;

use App\Models\{Collection, Purchase, Reader, SalesLedger};
use App\Services\Purchases\PurchaseService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Http};

class CheckBookPurchases extends Command
{
    private string $step = 'initialization';
    protected $signature = 'shelf:check-purchases {--simulate-after-backup : Run isolated synthetic cases, then roll back every test row}';
    protected $description = 'Verify per-book purchases through the live HTTP kernel with synthetic REST responses; never charge money.';

    public function handle(): int
    {
        if (! $this->option('simulate-after-backup')) {
            $this->info('Purchase configuration: '.(\App\Services\Purchases\RevenueCatClient::configured() ? 'sandbox configured' : 'disabled pending Shelf RevenueCat setup'));
            $this->info('No global access. No real payments enabled.');
            return self::SUCCESS;
        }
        $before = [];
        foreach (['readers', 'collections', 'poems', 'purchases', 'purchase_events', 'sales_ledger', 'book_entitlements', 'purchase_consents', 'personal_access_tokens'] as $table) {
            $before[$table] = DB::table($table)->count();
        }
        $version = DB::table('app_settings')->where('key', 'content_version')->value('value');
        $transactionLevel = DB::transactionLevel();
        DB::beginTransaction();
        try {
            // Overrides only this CLI process. Never enable a fake verifier on the website.
            $authorization = 'Bearer '.bin2hex(random_bytes(32));
            config(['reader_auth.enabled' => true, 'purchases.enabled' => true, 'purchases.secret_key' => bin2hex(random_bytes(32)),
                'purchases.public_sdk_key' => 'goog_synthetic', 'purchases.webhook_authorization' => $authorization, 'purchases.app_id' => 'shelf-synthetic']);
            $tag = bin2hex(random_bytes(8));
            $reader = Reader::create(['email' => 'shelf-purchase-'.$tag.'@example.test']);
            $other = Reader::create(['email' => 'shelf-other-'.$tag.'@example.test']);
            foreach ([$reader, $other] as $identity) { $identity->forceFill(['email_verified_at' => now()])->save(); }
            config(['purchases.test_reader_ids' => [$reader->id, $other->id]]);
            $book = Collection::create(['title' => 'Synthetic purchase verification '.$tag, 'status' => 'published', 'price_usd' => '2.99']);
            $item = $book->poems()->create(['body' => "Synthetic sample\nSynthetic paid text", 'excerpt' => '', 'is_active' => true,
                'sample_mode' => 'partial', 'sample_unit' => 'lines', 'sample_count' => 1]);
            $token = $reader->createToken('synthetic', ['reader'], now()->addHour())->plainTextToken;
            $otherToken = $other->createToken('synthetic', ['reader'], now()->addHour())->plainTextToken;
            $subscriber = ['original_app_user_id' => (string) $reader->id,
                'entitlements' => [$book->product_id => ['product_identifier' => $book->product_id, 'expires_date' => null]],
                'non_subscriptions' => [$book->product_id => [['id' => 'synthetic-rc-id', 'store' => 'play_store', 'is_sandbox' => true, 'purchase_date' => now()->toIso8601String()]]]];
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::preventStrayRequests();
            Http::fake(['api.revenuecat.com/*' => Http::response(['subscriber' => $subscriber])]);
            $this->probe('POST', '/api/library/books/'.$book->id.'/consent', ['agree' => true], 'Bearer '.$token, 200);
            $event = ['id' => 'synthetic-sale-'.$tag, 'type' => 'NON_RENEWING_PURCHASE', 'environment' => 'SANDBOX', 'store' => 'PLAY_STORE',
                'app_id' => 'shelf-synthetic', 'app_user_id' => (string) $reader->id, 'product_id' => $book->product_id,
                'transaction_id' => 'GPA.synthetic-'.$tag, 'purchased_at_ms' => now()->getTimestampMs(), 'event_timestamp_ms' => now()->getTimestampMs(),
                'currency' => 'USD', 'price_in_purchased_currency' => '2.99'];
            $this->probe('POST', '/api/purchases/webhook', ['event' => $event], '', 401);
            foreach ([$event, $event, array_replace($event, ['id' => 'synthetic-duplicate-'.$tag])] as $payload) {
                $this->probe('POST', '/api/purchases/webhook', ['event' => $payload], $authorization, 200);
            }
            if (Purchase::where('reader_id', $reader->id)->count() !== 1 || SalesLedger::whereHas('purchase', fn ($q) => $q->where('reader_id', $reader->id))->count() !== 1) {
                throw new \RuntimeException('Duplicate purchase/ledger entry.');
            }
            $this->probe('GET', '/api/library/poems/'.$item->id, [], 'Bearer '.$token, 200);
            $this->probe('GET', '/api/library/poems/'.$item->id, [], 'Bearer '.$otherToken, 404);
            $this->probe('GET', '/api/library/poems/'.$item->id, [], '', 401);
            $this->probe('GET', '/admin', [], 'Bearer '.$token, 401);
            $book->changeStatus('withdrawn');
            $this->probe('GET', '/api/collections/'.$book->slug, [], '', 404);
            $this->probe('GET', '/api/library/poems/'.$item->id, [], 'Bearer '.$token, 200);
            $restored = $this->probe('POST', '/api/library/restore', [], 'Bearer '.$token, 200);
            if (($restored['books'][0]['id'] ?? null) !== $book->id) { throw new \RuntimeException('Restore failed.'); }
            foreach (['CANCELLATION', 'EXPIRATION'] as $type) {
                $reversal = array_replace($event, ['id' => 'synthetic-'.$type.'-'.$tag, 'type' => $type]);
                $this->probe('POST', '/api/purchases/webhook', ['event' => $reversal], $authorization, 200);
                $this->probe('POST', '/api/purchases/webhook', ['event' => $reversal], $authorization, 200);
                $this->probe('GET', '/api/library/poems/'.$item->id, [], 'Bearer '.$token, 404);
            }
            $restored = $this->probe('POST', '/api/library/restore', [], 'Bearer '.$token, 200);
            if ($restored['books'] !== []) { throw new \RuntimeException('Restore revived revoked access.'); }
            $this->info('PASS: authenticated purchase + REST, duplicate event/transaction, other reader blocked, withdrawn access, restore, refund, revoke, admin blocked.');
        } catch (\Throwable $error) {
            $this->error('Purchase simulation failed at '.$this->step.' ('.class_basename($error).'); no exception details or tokens printed.');
            return self::FAILURE;
        } finally {
            while (DB::transactionLevel() > $transactionLevel) { DB::rollBack(); }
            app('auth')->forgetGuards();
        }
        foreach ($before as $table => $count) {
            if (DB::table($table)->count() !== $count) { $this->error('Cleanup verification failed: '.$table); return self::FAILURE; }
        }
        if (DB::table('app_settings')->where('key', 'content_version')->value('value') !== $version) {
            $this->error('Content-version rollback failed.'); return self::FAILURE;
        }
        $this->info('PASS: all synthetic rows rolled back; existing reader/catalogue/payment records unchanged. No real RevenueCat/Play transaction attempted.');
        return self::SUCCESS;
    }

    private function probe(string $method, string $path, array $data, string $authorization, int $status): array
    {
        $this->step = $method.' '.$path;
        app('auth')->forgetGuards();
        $request = Request::create(url($path), $method, [], [], [], ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $authorization], json_encode($data));
        $response = app(Kernel::class)->handle($request);
        if ($response->getStatusCode() !== $status) { $this->step .= ' expected '.$status.', received '.$response->getStatusCode(); throw new \RuntimeException('Unexpected HTTP result.'); }
        return json_decode($response->getContent(), true) ?? [];
    }
}
