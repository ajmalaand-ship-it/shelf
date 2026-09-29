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
        RateLimiter::for('reader-auth', function (Request $request) {
            $email = is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : '';

            return [Limit::perMinute(30)->by('reader-ip:'.$request->ip()),
                Limit::perMinute(5)->by('reader-action:'.$request->path().':'.hash('sha256', $email ?: (string) $request->ip()))];
        });
        Livewire::addPersistentMiddleware([RejectNonOwnerAdmin::class]);
    }
}
