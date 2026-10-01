<?php

namespace App\Providers;

use App\Http\Middleware\RejectNonOwnerAdmin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (\App\Support\Staging::active()) {
            if (! app()->runningUnitTests() && (config('database.connections.mysql.database') !== 'shelf_staging'
                || app()->environment() !== 'staging')) {
                throw new \RuntimeException('Staging environment/database boundary is invalid.');
            }
            config(['play_sync.enabled' => false, 'play_sync.credentials_path' => null,
                'mail.default' => 'log', 'queue.default' => 'null', 'queue.connections.database.queue' => 'staging-disabled']);
            foreach (array_keys(config('mail.mailers')) as $mailer) {
                config(['mail.mailers.'.$mailer => ['transport' => 'log']]);
            }
            \Illuminate\Support\Facades\Event::listen(\Illuminate\Queue\Events\JobProcessing::class,
                fn () => throw new \RuntimeException('Staging queue workers are disabled.'));
        }
        RateLimiter::for('reader-auth', function (Request $request) {
            $email = is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : '';

            return [Limit::perMinute(30)->by('reader-ip:'.$request->ip()),
                Limit::perMinute(5)->by('reader-action:'.$request->path().':'.hash('sha256', $email ?: (string) $request->ip()))];
        });
        Livewire::addPersistentMiddleware([RejectNonOwnerAdmin::class]);
    }
}
