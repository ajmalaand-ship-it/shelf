<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RejectNonOwnerAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_if($request->user() && ! $request->user()->is_owner, 403);

        return $next($request);
    }
}
