<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RevenueCatEntitlementService
{
    public const USER_HEADER = 'X-RC-User-Id';

    public const REFRESH_HEADER = 'X-RC-Refresh';

    public function requestIsEntitled(Request $request): bool
    {
        if ($request->attributes->has('revenuecat_entitled')) {
            return (bool) $request->attributes->get('revenuecat_entitled');
        }

        $userId = $request->header(self::USER_HEADER);
        $entitled = is_string($userId) && $this->isValidUserId($userId)
            ? $this->isEntitled($userId, $request->header(self::REFRESH_HEADER) === '1')
            : false;
        $request->attributes->set('revenuecat_entitled', $entitled);

        return $entitled;
    }

    public function isEntitled(string $userId, bool $forceRefresh = false): bool
    {
        if (! $this->isValidUserId($userId)) {
            return false;
        }

        $secret = (string) config('services.revenuecat.secret_api_key');
        if ($secret === '') {
            return false;
        }

        $cacheKey = $this->cacheKey($userId);
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        } elseif (($cached = Cache::get($cacheKey)) !== null) {
            return $cached === 'entitled';
        }

        $reference = substr(hash('sha256', $userId), 0, 12);
        try {
            $response = Http::acceptJson()
                ->withToken($secret)
                ->timeout((int) config('services.revenuecat.timeout_seconds', 4))
                ->get('https://api.revenuecat.com/v1/subscribers/'.rawurlencode($userId));

            if (! $response->successful()) {
                Log::warning('RevenueCat entitlement check failed', [
                    'customer_ref' => $reference,
                    'status_category' => $response->status(),
                ]);
                $this->cache(false, $cacheKey);

                return false;
            }

            $entitlement = $response->json('subscriber.entitlements.'.config('services.revenuecat.entitlement'));
            if ($entitlement !== null && ! is_array($entitlement)) {
                Log::warning('RevenueCat entitlement response malformed', ['customer_ref' => $reference]);
                $this->cache(false, $cacheKey);

                return false;
            }

            $entitled = is_array($entitlement) && $this->isActive($entitlement);
            $this->cache($entitled, $cacheKey);

            return $entitled;
        } catch (Throwable $error) {
            Log::warning('RevenueCat entitlement provider unavailable', [
                'customer_ref' => $reference,
                'error_class' => $error::class,
            ]);
            $this->cache(false, $cacheKey);

            return false;
        }
    }

    public function isValidUserId(string $userId): bool
    {
        $length = strlen($userId);
        if ($length < 1 || $length > 100 || str_contains($userId, '/') || preg_match('/[\x00-\x1F\x7F]/', $userId)) {
            return false;
        }

        return ! in_array(strtolower($userId), [
            'no_user', 'null', 'none', 'nil', '(null)', 'nan', 'unidentified',
            'undefined', 'unknown', 'anonymous', 'guest', '-1', '0', '[]', '{}',
            '[object object]',
        ], true);
    }

    private function isActive(array $entitlement): bool
    {
        $expires = $entitlement['expires_date'] ?? null;
        if ($expires === null) {
            return true;
        }

        return is_string($expires) && strtotime($expires) !== false && strtotime($expires) > time();
    }

    private function cache(bool $entitled, string $key): void
    {
        $seconds = $entitled
            ? (int) config('services.revenuecat.positive_ttl_seconds', 86400)
            : (int) config('services.revenuecat.negative_ttl_seconds', 300);
        Cache::put($key, $entitled ? 'entitled' : 'not_entitled', now()->addSeconds($seconds));
    }

    private function cacheKey(string $userId): string
    {
        return 'revenuecat:entitlement:'.hash_hmac('sha256', $userId, (string) config('app.key'));
    }
}
