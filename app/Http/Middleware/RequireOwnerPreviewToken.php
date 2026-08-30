<?php

namespace App\Http\Middleware;

use App\Services\OwnerPreviewTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOwnerPreviewToken
{
    public function __construct(private readonly OwnerPreviewTokenService $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $valid = $this->tokens->valid($request->bearerToken());
        } catch (\RuntimeException) {
            $valid = false;
        }

        abort_unless($valid, 401, 'Owner preview authorization required.');

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
