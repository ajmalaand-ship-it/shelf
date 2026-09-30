<?php

namespace App\Services\Purchases;

use App\Models\Collection;
use App\Models\Reader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class RevenueCatClient
{
    public static function configured(): bool
    {
        return config('purchases.enabled') && filled(config('purchases.secret_key'))
            && filled(config('purchases.webhook_authorization')) && filled(config('purchases.app_id'))
            && str_starts_with((string) config('purchases.public_sdk_key'), 'goog_');
    }

    public function subscriber(Reader $reader): array
    {
        abort_unless(self::configured(), 503, 'Purchases are not configured yet.');
        try {
            $response = Http::withToken(config('purchases.secret_key'))->acceptJson()->connectTimeout(5)->timeout(12)
                ->get('https://api.revenuecat.com/v1/subscribers/'.rawurlencode((string) $reader->id));
            $subscriber = $response->successful() ? $response->json('subscriber') : null;
            if (! is_array($subscriber) || ($subscriber['original_app_user_id'] ?? null) !== (string) $reader->id
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
