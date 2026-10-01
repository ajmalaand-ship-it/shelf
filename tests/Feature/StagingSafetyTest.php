<?php

namespace Tests\Feature;

use App\Models\{Author, Collection, Reader, User};
use App\Services\StagingCatalogue;
use App\Services\Play\{GooglePlayClient, PlaySyncException};
use App\Support\Staging;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Http};
use Tests\TestCase;

class StagingSafetyTest extends TestCase
{
    use RefreshDatabase;

    private function staging(): void
    {
        config(['staging.testing' => true, 'staging.access_key' => str_repeat('a', 48)]);
    }

    public function test_private_gate_marker_noindex_banner_and_cross_environment_webhooks(): void
    {
        $this->staging();
        $this->getJson('/api/app-config')->assertUnauthorized();
        $headers = ['X-Shelf-Test-Key' => str_repeat('a', 48)];
        $this->getJson('/api/app-config', $headers)->assertOk()->assertJsonPath('environment', 'staging')
            ->assertHeader('X-Shelf-Environment', 'staging')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->get('/admin/login', $headers)->assertOk()->assertSee('TEST COPY');
        $this->postJson('/api/purchases/webhook', [], $headers)->assertForbidden();
        $this->assertSame('staging_7', Staging::identity(7));
        config(['staging.testing' => false]);
        $this->assertSame('7', Staging::identity(7));
        try { Staging::readerId('staging_7'); $this->fail('Staging identity accepted in production'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(422, $e->getStatusCode()); }
        $this->assertSame(0, DB::table('purchases')->count());
    }

    public function test_forced_play_settings_and_direct_client_can_never_send_on_staging(): void
    {
        $this->staging(); Http::preventStrayRequests(); Http::fake();
        config(['play_sync.enabled' => true, 'play_sync.credentials_path' => '/home/shelf/secrets/not-used.json']);
        $book = Collection::create(['title' => 'Synthetic staging book', 'price_usd' => '2.99']);
        $this->assertFalse(GooglePlayClient::configured());
        try { (new GooglePlayClient)->sync($book); $this->fail('Staging sync allowed'); }
        catch (PlaySyncException $e) { $this->assertStringContainsString('permanently disabled', $e->getMessage()); }
        Http::assertNothingSent();
        (new \App\Providers\AppServiceProvider($this->app))->boot();
        $this->assertSame('log', config('mail.default'));
        foreach (config('mail.mailers') as $mailer) { $this->assertSame('log', $mailer['transport']); }
        $this->assertSame('null', config('queue.default'));
        $root=env('SHELF_TEST_TMP').'/staging-runner';mkdir($root.'/scripts',0700,true);
        file_put_contents($root.'/.shelf-staging','test');
        copy(base_path('scripts/run_play_price_sync.sh'),$root.'/scripts/run_play_price_sync.sh');
        $process=new \Symfony\Component\Process\Process(['bash',$root.'/scripts/run_play_price_sync.sh']);
        $process->mustRun();$this->assertStringContainsString('production queue runner disabled',$process->getOutput());
    }

    public function test_refresh_allowlist_preserves_source_text_and_excludes_all_personal_and_job_rows(): void
    {
        User::factory()->state(['is_owner' => true])->create();
        User::factory()->create();
        $reader = Reader::create(['email' => 'never-copy@example.test']);
        $reader->createToken('not-copied');
        $book = Collection::create(['title' => 'Exact source', 'status' => 'draft']);
        $book->poems()->create(['body' => "کرښه\nExact Unicode\n", 'excerpt' => '']);
        DB::table('app_settings')->insert(['key' => 'private_secret_synthetic', 'value' => 'do-not-copy']);
        $service = new StagingCatalogue;
        $data = $service->capture(DB::connection());
        $this->assertSame(StagingCatalogue::TABLES, array_keys($data));
        $this->assertCount(1, $data['users']);
        $this->assertArrayNotHasKey('readers', $data);
        $this->assertArrayNotHasKey('purchases', $data);
        $this->assertArrayNotHasKey('jobs', $data);
        $this->assertNotContains('private_secret_synthetic', array_column($data['app_settings'], 'key'));
        $this->assertSame("کرښه\nExact Unicode\n", $data['poems'][0]['body']);
        try { $service->apply($data); $this->fail('Production refresh allowed'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('only on', $e->getMessage()); }
        $this->staging();
        $service->apply($data);
        $this->assertDatabaseHas('readers', ['email' => 'never-copy@example.test']); // existing stage identities preserved
        $this->assertSame("کرښه\nExact Unicode\n", DB::table('poems')->value('body'));
        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_staging_rest_uses_prefix_rejects_numeric_subscriber_and_confirms_sandbox_only(): void
    {
        $this->staging();
        $path = env('SHELF_TEST_TMP').'/stage-verifier.txt';file_put_contents($path, 'synthetic');chmod($path,0600);
        config(['purchases.enabled'=>true,'purchases.staging_project_confirmed'=>true,'purchases.secret_key_path'=>$path,
            'purchases.public_sdk_key'=>'goog_synthetic','purchases.webhook_authorization'=>'unused','purchases.app_id'=>'separate-stage-app']);
        $reader = Reader::create(['email'=>'stage@example.test']);
        $client = new \App\Services\Purchases\RevenueCatClient;
        Http::preventStrayRequests();
        Http::fake(['api.revenuecat.com/*'=>Http::response(['subscriber'=>[
            'original_app_user_id'=>(string)$reader->id,'entitlements'=>[],'non_subscriptions'=>[]]])]);
        try { $client->subscriber($reader);$this->fail('Production subscriber accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(503,$e->getStatusCode()); }
        Http::assertSent(fn ($r)=>str_ends_with($r->url(),'/staging_'.$reader->id));
        $book=Collection::create(['title'=>'Staging paid book','status'=>'published','price_usd'=>'2.99']);
        $reader->forceFill(['email_verified_at'=>now()])->save();
        config(['reader_auth.enabled'=>true,'purchases.test_reader_ids'=>[$reader->id]]);
        DB::table('purchase_consents')->insert(['reader_id'=>$reader->id,'collection_id'=>$book->id,
            'wording'=>\App\Services\Purchases\PurchaseService::CONSENT,'created_at'=>now()->subMinute()]);
        $subscriber=['original_app_user_id'=>'staging_'.$reader->id,
            'entitlements'=>[$book->product_id=>['product_identifier'=>$book->product_id,'expires_date'=>null]],
            'non_subscriptions'=>[$book->product_id=>[['id'=>'separate-stage-transaction','store'=>'play_store',
                'is_sandbox'=>true,'purchase_date'=>now()->toIso8601String()]]]];
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.revenuecat.com/*'=>Http::response(['subscriber'=>$subscriber])]);
        $service=app(\App\Services\Purchases\PurchaseService::class);
        $service->reconcile($reader);$service->reconcile($reader);
        $this->assertDatabaseCount('purchases',1);$this->assertDatabaseCount('sales_ledger',1);
        $this->assertDatabaseHas('book_entitlements',['reader_id'=>$reader->id,'collection_id'=>$book->id,'active'=>true]);
        $subscriber['non_subscriptions'][$book->product_id][0]['refunded_at']=now()->toIso8601String();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.revenuecat.com/*'=>Http::response(['subscriber'=>$subscriber])]);
        $service->reconcile($reader);
        $this->assertDatabaseHas('book_entitlements',['active'=>false]);
        $this->assertDatabaseHas('sales_ledger',['status'=>'revoke']);
        config(['purchases.app_id'=>'appf83cd58c5c']);
        $this->assertFalse($client::configured());
        unlink($path);
    }
}
