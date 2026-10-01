<?php

namespace App\Http\Middleware;

use App\Support\Staging;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StagingAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! Staging::active()) {
            return $next($request);
        }
        $headers = ['X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'X-Shelf-Environment' => 'staging', 'Cache-Control' => 'private, no-store'];
        $key = config('staging.access_key');
        $mobile = is_string($key) && strlen($key) >= 32
            && hash_equals($key, (string) $request->header('X-Shelf-Test-Key'));
        $hash = config('staging.basic_password_hash');
        $browser = $request->getUser() === 'owner' && is_string($hash) && filled($hash)
            && Hash::check((string) $request->getPassword(), $hash);
        if (! $mobile && ! $browser) {
            return response('Private Shelf test copy.', 401, $headers + ['WWW-Authenticate' => 'Basic realm="Shelf Test"']);
        }
        $response = $next($request);
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }
        return $response;
    }
}
