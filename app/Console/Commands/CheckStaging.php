<?php

namespace App\Console\Commands;

use App\Support\Staging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Http, Mail};

class CheckStaging extends Command
{
    protected $signature = 'shelf:check-staging {--initial : Assert no imported reader or payment data}';
    protected $description = 'Prove staging isolation and safe side effects, with rolled-back staging writes.';
    public function handle(): int
    {
        if (! Staging::active() || DB::connection()->getDatabaseName() !== 'shelf_staging'
            || realpath(storage_path()) === realpath('/home/shelf/apps/shelf/storage')
            || config('session.cookie') !== 'shelf_staging_session' || config('cache.prefix') !== 'shelf_staging_') {
            $this->error('FAIL: environment/database/files/session/cache boundary.'); return self::FAILURE;
        }
        $this->info('PASS: separate staging database, files, session and cache names.');
        if ($this->option('initial')) {
            foreach (['readers', 'reader_account_actions', 'personal_access_tokens', 'purchases', 'sales_ledger', 'purchase_events', 'sessions', 'jobs'] as $table) {
                if (DB::table($table)->exists()) { $this->error('FAIL: unexpected copied personal/job data.'); return self::FAILURE; }
            }
            if (DB::table('users')->count() !== 1 || ! DB::table('users')->where('is_owner', true)->exists()) { return self::FAILURE; }
            $this->info('PASS: owner only; no production reader, payment, session or job data.');
        }
        Http::preventStrayRequests();
        config(['play_sync.enabled' => true, 'play_sync.credentials_path' => '/home/shelf/secrets/not-readable-on-staging']);
        if (\App\Services\Play\GooglePlayClient::configured()) { return self::FAILURE; }
        try {
            (new \App\Services\Play\GooglePlayClient)->sync(\App\Models\Collection::firstOrFail());
            return self::FAILURE;
        } catch (\App\Services\Play\PlaySyncException) {}
        Http::assertNothingSent();
        if (config('mail.default') !== 'log' || config('queue.default') !== 'null') { return self::FAILURE; }
        Mail::raw('Shelf staging isolation check; no reader delivery.', fn ($m) => $m->to('never-deliver@example.test')->subject('Shelf Test'));
        $this->info('PASS: forced-on Play sync blocked before HTTP; all mail logged; queue disabled.');
        $level = DB::transactionLevel(); DB::beginTransaction();
        try {
            DB::table('app_settings')->updateOrInsert(['key' => 'staging_isolation_probe'], ['value' => 'test-copy-only']);
            if (DB::table('app_settings')->where('key', 'staging_isolation_probe')->value('value') !== 'test-copy-only') { return self::FAILURE; }
            $email='staging-check-'.bin2hex(random_bytes(8)).'@example.test';
            $password='Stage!'.bin2hex(random_bytes(16)).'A9';
            $probe=function (string $path,array $data,?string $bearer=null,string $method='POST'): array {
                app('auth')->forgetGuards();
                $request=\Illuminate\Http\Request::create(url($path),$method,[],[],[],[
                    'HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json',
                    'HTTP_X_SHELF_TEST_KEY'=>(string)config('staging.access_key'),
                    'HTTP_AUTHORIZATION'=>$bearer ? 'Bearer '.$bearer : '',
                ],json_encode($data));
                $response=app(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
                return [$response->getStatusCode(),json_decode($response->getContent(),true)];
            };
            [$status]=$probe('/api/auth/register',['email'=>$email,'password'=>$password,'password_confirmation'=>$password]);
            if ($status!==202) { $this->error('FAIL: staging registration.');return self::FAILURE; }
            [$status,$json]=$probe('/api/auth/login',['email'=>$email,'password'=>$password]);
            if ($status!==200 || ! is_string($json['token']??null)) { $this->error('FAIL: staging email/password login.');return self::FAILURE; }
            [$status,$profile]=$probe('/api/auth/me',[],$json['token'],'GET');
            if ($status!==200 || ($profile['user']['email']??null)!==$email) { return self::FAILURE; }
            $this->info('PASS: direct staging register, logged verification email, email/password login and authenticated account; synthetic data rolled back.');
        } finally { while (DB::transactionLevel() > $level) { DB::rollBack(); } }
        $this->info('PASS: staging data mutation tested and rolled back.');
        $name='staging-isolation-'.bin2hex(random_bytes(8)).'.txt';
        $path=storage_path('app/private/'.$name);
        try {
            file_put_contents($path,'test copy only');
            if (file_exists('/home/shelf/apps/shelf/storage/app/private/'.$name)) { return self::FAILURE; }
        } finally { if (is_file($path)) { unlink($path); } }
        $this->info('PASS: staging file write isolated from production and cleaned up.');
        return self::SUCCESS;
    }
}
