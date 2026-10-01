<?php

namespace App\Services\Purchases;

use App\Models\Collection;
use App\Models\Reader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class RevenueCatClient
{
    private static function credential(): ?string
    {
        $path = config('purchases.secret_key_path');
        if (! $path) {
            return config('purchases.secret_key');
        }
        $real = is_string($path) ? realpath($path) : false;
        if (! $real || $real !== $path || ! is_file($real) || ! is_readable($real)
            || ! str_starts_with($real, '/home/shelf/') || str_starts_with($real, base_path().'/')
            || str_starts_with($real, '/home/shelf/public_html/') || (fileperms($real) & 0077) !== 0) {
            return null;
        }

        return trim(file_get_contents($real)) ?: null;
    }

    public static function configured(): bool
    {
        if (\App\Support\Staging::active() && (! config('purchases.staging_project_confirmed')
            || config('purchases.app_id') === 'appf83cd58c5c'
            || (! str_starts_with((string) config('purchases.secret_key_path'), '/home/shelf/secrets/staging/')
                && ! (app()->runningUnitTests() && str_starts_with((string) config('purchases.secret_key_path'), env('SHELF_TEST_TMP').'/'))))) {
            return false;
        }
        return config('purchases.enabled') && filled(self::credential())
            && filled(config('purchases.webhook_authorization')) && filled(config('purchases.app_id'))
            && str_starts_with((string) config('purchases.public_sdk_key'), 'goog_');
    }

    public function subscriber(Reader $reader): array
    {
        abort_unless(self::configured(), 503, 'Purchases are not configured yet.');
        try {
            $response = Http::withToken(self::credential())->acceptJson()->withoutRedirecting()->connectTimeout(5)->timeout(12)
                ->get('https://api.revenuecat.com/v1/subscribers/'.rawurlencode(\App\Support\Staging::identity($reader->id)));
            $subscriber = $response->successful() ? $response->json('subscriber') : null;
            if (! is_array($subscriber) || ($subscriber['original_app_user_id'] ?? null) !== \App\Support\Staging::identity($reader->id)
                || ! is_array($subscriber['entitlements'] ?? null) || ! is_array($subscriber['non_subscriptions'] ?? null)) {
                abort(503, 'Store confirmation is unavailable. Please try again.');
            }
            return $subscriber;
        } catch (\Illuminate\Http\Client\ConnectionException) {
            abort(503, 'Store confirmation is unavailable. Please try again.');
        }
    }

    public function confirms(array $subscriber, Collection $book, \DateTimeInterface $purchasedAt): bool
    {
        $entitlement = $subscriber['entitlements'][$book->product_id] ?? null;
        // Shelf books are permanent, non-subscription entitlements. Never accept a promo/global grant.
        if (! is_array($entitlement) || ($entitlement['product_identifier'] ?? null) !== $book->product_id
            || ! array_key_exists('expires_date', $entitlement) || $entitlement['expires_date'] !== null) {
            return false;
        }
        foreach ($subscriber['non_subscriptions'][$book->product_id] ?? [] as $purchase) {
            if (($purchase['store'] ?? null) !== 'play_store' || ($purchase['is_sandbox'] ?? null) !== true
                || ! empty($purchase['refunded_at']) || ! is_string($purchase['purchase_date'] ?? null)) {
                continue;
            }
            try {
                // API v1 non-subscription IDs are RevenueCat IDs, not Google transaction IDs.
                // Match the authenticated webhook's store/product/purchase time instead.
                if (abs(CarbonImmutable::parse($purchase['purchase_date'])->getTimestamp() - $purchasedAt->getTimestamp()) <= 1) {
                    return true;
                }
            } catch (\Throwable) {
                continue;
            }
        }
        return false;
    }
}
