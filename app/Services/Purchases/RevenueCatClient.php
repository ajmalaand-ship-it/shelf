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

    public static function productionEnabled(): bool
    {
        return ! \App\Support\Staging::active() && config('purchases.production_enabled') === true;
    }

    /** Classify only the matching server-fetched Play transaction, never a client flag. */
    public function purchaseEnvironment(array $subscriber, Collection $book, \DateTimeInterface $purchasedAt): ?string
    {
        $transactions = $subscriber['non_subscriptions'][$book->product_id] ?? [];
        abort_unless(is_array($transactions), 503, 'Store confirmation is incomplete. Please try again.');
        $matches = [];
        foreach ($transactions as $purchase) {
            abort_unless(is_array($purchase) && is_string($purchase['purchase_date'] ?? null)
                && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $purchase['purchase_date']), 503,
                'Store confirmation is incomplete. Please try again.');
            try {
                $date = CarbonImmutable::parse($purchase['purchase_date']);
            } catch (\Throwable) {
                abort(503, 'Store confirmation is incomplete. Please try again.');
            }
            if (abs($date->getTimestamp() - $purchasedAt->getTimestamp()) > 1) { continue; }
            abort_unless(($purchase['store'] ?? null) === 'play_store'
                && is_bool($purchase['is_sandbox'] ?? null), 503,
                'Store transaction environment is unverified. Please try again.');
            $matches[] = $purchase;
        }
        // API v1 IDs are RevenueCat IDs, not Google order IDs. An ambiguous time
        // match cannot independently verify a particular webhook transaction.
        abort_if(count($matches) > 1, 503, 'Store confirmation is ambiguous. Please try again.');
        if (! $matches || ! empty($matches[0]['refunded_at'])) { return null; }
        return $matches[0]['is_sandbox'] ? 'SANDBOX' : 'PRODUCTION';
    }

    public function confirms(array $subscriber, Collection $book, \DateTimeInterface $purchasedAt, string $environment = 'SANDBOX'): bool
    {
        abort_unless(in_array($environment, ['SANDBOX', 'PRODUCTION'], true), 503);
        $verified = $this->purchaseEnvironment($subscriber, $book, $purchasedAt);
        abort_if($verified !== null && $verified !== $environment, 503, 'Store transaction environment conflicts with recorded evidence.');
        $entitlement = $subscriber['entitlements'][$book->product_id] ?? null;
        abort_if($entitlement !== null && (! is_array($entitlement)
            || ! array_key_exists('expires_date', $entitlement)
            || ($entitlement['product_identifier'] ?? null) !== $book->product_id), 503,
            'Store entitlement mapping is incomplete. Please try again.');
        // Shelf books are permanent, non-subscription entitlements. No promo/global grant.
        return $verified === $environment && is_array($entitlement)
            && ($entitlement['product_identifier'] ?? null) === $book->product_id
            && array_key_exists('expires_date', $entitlement) && $entitlement['expires_date'] === null;
    }
}
