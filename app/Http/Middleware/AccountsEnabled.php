<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AccountsEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('reader_auth.enabled'), 503, 'Accounts are not available yet.');

        return $next($request);
    }
}
