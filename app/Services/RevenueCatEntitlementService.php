<?php

namespace App\Services;

use Illuminate\Http\Request;

/** Retired global access API. Never contact the provider or reuse its cache. */
class RevenueCatEntitlementService
{
    public function requestIsEntitled(Request $request): bool
    {
        return false;
    }

    public function isEntitled(string $userId, bool $forceRefresh = false): bool
    {
        return false;
    }
}
