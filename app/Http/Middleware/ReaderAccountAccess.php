<?php

namespace App\Http\Middleware;

use App\Models\Reader;
use Closure;
use Illuminate\Http\Request;

class ReaderAccountAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() instanceof Reader && $request->user()->tokenCan('reader'), 403);

        return $next($request);
    }
}
