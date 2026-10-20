<?php

namespace App\Services\Purchases;

use App\Models\{Collection, Reader};
use Illuminate\Support\Facades\Http;

/** Server-to-server V2 evidence. No mobile receipt/environment is authoritative. */
class ApplePurchaseProof
{
    public static function configured(): bool
    {
        return config('purchases.apple_sandbox_enabled') === true
            && ! \App\Support\Staging::active()
            && str_starts_with((string) config('purchases.apple_public_sdk_key'), 'appl_')
            && filled(config('purchases.apple_app_id')) && filled(config('purchases.apple_product_id'))
            && filled(config('purchases.apple_project_id')) && filled(config('purchases.apple_webhook_authorization'))
            && filled(RevenueCatClient::appleCredential());
    }

    // Include withdrawn/binned mappings for existing purchase/refund verification.
    // Discovery and new-sale eligibility are checked separately.
    public static function mappedBook(mixed $product): ?Collection
    {
        if (! is_string($product) || ! preg_match('/^shelf_book_([1-9][0-9]*)$/D', $product, $match)) {
            return null;
        }
        return Collection::withTrashed()->whereKey($match[1])->where('product_id', $product)->first();
    }

    public static function catalogueProducts(): array
    {
        return Collection::where('status', 'published')->where('price_usd', '>', 0)
            ->orderBy('id')->get(['id', 'product_id'])->filter(fn ($book) =>
                $book->product_id === 'shelf_book_'.$book->id)->pluck('product_id')->values()->all();
    }

    public static function credentialsConfigured(): bool
    {
        return ! \App\Support\Staging::active()
            && str_starts_with((string) config('purchases.apple_public_sdk_key'), 'appl_')
            && filled(config('purchases.apple_app_id')) && filled(config('purchases.apple_project_id'))
            && filled(config('purchases.apple_webhook_authorization')) && filled(RevenueCatClient::appleCredential());
    }

    public static function customersConfigured(): bool
    {
        return config('purchases.apple_customers_enabled') === true && self::credentialsConfigured();
    }

    public static function customerCheckoutEnabled(): bool
    {
        return self::customersConfigured() && (RevenueCatClient::productionEnabled()
            || config('purchases.apple_customer_sandbox_enabled') === true);
    }

    public static function customerPurchase(\App\Models\Purchase $purchase): bool
    {
        return $purchase->store === 'APP_STORE' && ($purchase->environment === 'PRODUCTION'
            || \App\Models\PurchaseEvent::where('store', 'APP_STORE')->where('transaction_id', $purchase->transaction_id)
                ->where('collection_id', $purchase->collection_id)->where('details->customer_purchase', true)->exists());
    }

    public static function canVerify(\App\Models\Purchase $purchase): bool
    {
        return self::customerPurchase($purchase) ? self::credentialsConfigured() : self::configured();
    }

    public static function customerAllowed(Reader $reader, bool $checkout = true): bool
    {
        return $reader->email_verified_at !== null && (! $checkout || ! $reader->buying_blocked);
    }

    public function customerTransaction(int $reader, string $transaction, int $at, ?string $product = null, ?string $environment = null): array
    {
        $items = $this->list($this->prefix().'/purchases', ['store_purchase_identifier' => $transaction]);
        abort_unless(count($items) === 1 && is_array($items[0]), 503, 'Apple transaction proof is missing or ambiguous.');
        return $this->validateCustomer($items[0], $reader, $transaction, $at, $product, $environment);
    }

    private function validateCustomer(array $p, int $reader, ?string $transaction = null, ?int $at = null, ?string $product = null, ?string $environment = null): array
    {
        abort_unless(self::credentialsConfigured() && ($p['object'] ?? null) === 'purchase'
            && is_string($p['id'] ?? null) && ($p['customer_id'] ?? null) === (string) $reader
            && ($p['original_customer_id'] ?? null) === (string) $reader
            && ($p['store'] ?? null) === 'app_store' && in_array($p['environment'] ?? null, ['sandbox', 'production'], true)
            && ($environment === null || strtoupper($p['environment']) === $environment)
            && ($p['ownership'] ?? null) === 'purchased' && ($p['quantity'] ?? null) === 1
            && is_string($p['product_id'] ?? null) && is_string($p['store_purchase_identifier'] ?? null)
            && preg_match('/^[0-9]{1,255}$/D', $p['store_purchase_identifier'])
            && ($transaction === null || $p['store_purchase_identifier'] === $transaction)
            && is_int($p['purchased_at'] ?? null) && $p['purchased_at'] > 0 && ($at === null || $p['purchased_at'] === $at)
            && in_array($p['status'] ?? null, ['owned', 'refunded'], true), 503, 'Apple transaction proof does not match.');
        $app = $this->get($this->prefix().'/apps/'.rawurlencode(config('purchases.apple_app_id')));
        $mapped = $this->get($this->prefix().'/products/'.rawurlencode($p['product_id']));
        $canonical = $mapped['store_identifier'] ?? null;
        abort_unless(($app['id'] ?? null) === config('purchases.apple_app_id')
            && ($app['project_id'] ?? null) === config('purchases.apple_project_id') && ($app['type'] ?? null) === 'app_store'
            && ($app['app_store']['bundle_id'] ?? null) === 'services.shelf.app'
            && ($mapped['id'] ?? null) === $p['product_id'] && ($mapped['app_id'] ?? null) === $app['id']
            && self::mappedBook($canonical) !== null && ($product === null || $canonical === $product)
            && (($mapped['type'] ?? null) === 'non_consumable'
                || (($mapped['type'] ?? null) === 'one_time' && ($mapped['one_time']['is_consumable'] ?? null) === false)),
            503, 'Apple app/product mapping is unverified.');
        if ($p['status'] === 'owned') {
            $entitlements = $this->list($this->prefix().'/purchases/'.rawurlencode($p['id']).'/entitlements');
            $matches = array_filter($entitlements, fn ($e) => is_array($e) && ($e['object'] ?? null) === 'entitlement'
                && ($e['state'] ?? null) === 'active' && ($e['project_id'] ?? null) === config('purchases.apple_project_id')
                && ($e['lookup_key'] ?? null) === $canonical);
            abort_unless(count($matches) === 1, 503, 'Apple book entitlement is unverified.');
        }
        $p['shelf_product_id'] = $canonical;
        return $p;
    }

    public function customerPurchases(Reader $reader): array
    {
        $result = [];
        foreach (['production', 'sandbox'] as $environment) {
            foreach ($this->list($this->prefix().'/customers/'.rawurlencode((string) $reader->id).'/purchases', ['environment' => $environment]) as $p) {
                abort_unless(is_array($p), 503);
                if (($p['store'] ?? null) !== 'app_store') { continue; }
                $result[] = $this->validateCustomer($p, $reader->id, environment: strtoupper($environment));
            }
        }
        return $result;
    }

    public static function owner(Reader $reader, bool $checkout = true): bool
    {
        if (! $reader->email_verified_at || ($checkout && $reader->buying_blocked)) { return false; }
        if (app()->runningUnitTests() && in_array($reader->id, config('purchases.test_reader_ids', []), true)) { return true; }
        return \App\Models\User::where('is_owner', true)->whereRaw('LOWER(email) = ?', [strtolower($reader->email)])->exists();
    }

    private function get(string $path, array $query = []): array
    {
        abort_unless(self::credentialsConfigured(), 503, 'Apple store verification is not configured.');
        try {
            $r = Http::withToken(RevenueCatClient::appleCredential())->acceptJson()->withoutRedirecting()
                ->connectTimeout(5)->timeout(12)->get('https://api.revenuecat.com'.$path, $query);
            if ($r->status() === 404 && preg_match('#^/v2/projects/[^/]+/customers/[1-9][0-9]*/purchases$#D', $path)) {
                return ['object' => 'list', 'items' => [], 'next_page' => null];
            }
            $data = $r->json();
            abort_unless($r->successful() && is_array($data), 503, 'Apple store proof is unavailable.');
            return $data;
        } catch (\Illuminate\Http\Client\ConnectionException) {
            abort(503, 'Apple store proof is unavailable.');
        }
    }

    private function prefix(): string
    {
        return '/v2/projects/'.rawurlencode(config('purchases.apple_project_id'));
    }

    private function list(string $path, array $query = []): array
    {
        $items = []; $next = $path; $seen = [];
        while ($next !== null) {
            abort_unless(is_string($next) && ($next === $path || str_starts_with($next, $path.'?'))
                && ! isset($seen[$next]) && count($seen) < 10, 503, 'Apple evidence pagination is unverified.');
            $seen[$next] = true;
            $data = $this->get($next, $next === $path ? $query : []);
            abort_unless(($data['object'] ?? null) === 'list' && is_array($data['items'] ?? null)
                && array_key_exists('next_page', $data), 503, 'Apple evidence is incomplete.');
            $items = array_merge($items, $data['items']);
            $next = $data['next_page'];
        }
        return $items;
    }

    private function mapping(): void
    {
        $app = $this->get($this->prefix().'/apps/'.rawurlencode(config('purchases.apple_app_id')));
        $product = $this->get($this->prefix().'/products/'.rawurlencode(config('purchases.apple_product_id')));
        abort_unless(($app['id'] ?? null) === config('purchases.apple_app_id')
            && ($app['project_id'] ?? null) === config('purchases.apple_project_id')
            && ($app['type'] ?? null) === 'app_store' && ($app['app_store']['bundle_id'] ?? null) === 'services.shelf.app'
            && ($product['id'] ?? null) === config('purchases.apple_product_id')
            && ($product['app_id'] ?? null) === config('purchases.apple_app_id')
            && ($product['store_identifier'] ?? null) === 'shelf_book_3'
            && (($product['type'] ?? null) === 'non_consumable'
                || (($product['type'] ?? null) === 'one_time' && ($product['one_time']['is_consumable'] ?? null) === false)),
            503, 'Apple app/product mapping is unverified.');
    }

    public function validate(array $p, int $reader, ?string $transaction = null, ?int $purchasedAt = null): array
    {
        abort_unless(($p['object'] ?? null) === 'purchase' && is_string($p['id'] ?? null)
            && ($p['customer_id'] ?? null) === ApplePurchaseSession::identity($reader, $transaction ?? (string) ($p['store_purchase_identifier'] ?? ''), $purchasedAt ?? (int) ($p['purchased_at'] ?? 0))
            && ($p['original_customer_id'] ?? null) === ($p['customer_id'] ?? null)
            && ($p['product_id'] ?? null) === config('purchases.apple_product_id')
            && ($p['store'] ?? null) === 'app_store' && ($p['environment'] ?? null) === 'sandbox'
            && ($p['ownership'] ?? null) === 'purchased' && ($p['quantity'] ?? null) === 1
            && is_string($p['store_purchase_identifier'] ?? null) && preg_match('/^[0-9]{1,255}$/D', $p['store_purchase_identifier'])
            && ($transaction === null || $p['store_purchase_identifier'] === $transaction)
            && is_int($p['purchased_at'] ?? null) && $p['purchased_at'] > 0
            && ($purchasedAt === null || $p['purchased_at'] === $purchasedAt)
            && in_array($p['status'] ?? null, ['owned', 'refunded'], true), 503, 'Apple transaction proof does not match.');
        if ($p['status'] === 'owned') {
            $entitlements = $this->list($this->prefix().'/purchases/'.rawurlencode($p['id']).'/entitlements');
            $matches = array_filter($entitlements, fn ($e) => is_array($e) && ($e['object'] ?? null) === 'entitlement' && ($e['state'] ?? null) === 'active'
                && ($e['project_id'] ?? null) === config('purchases.apple_project_id') && ($e['lookup_key'] ?? null) === 'shelf_book_3');
            abort_unless(count($matches) === 1, 503, 'Apple book entitlement is unverified.');
        }
        return $p;
    }

    public function transaction(int $reader, string $transaction, int $purchasedAt): array
    {
        $this->mapping();
        $items = $this->list($this->prefix().'/purchases', ['store_purchase_identifier' => $transaction]);
        abort_unless(count($items) === 1 && is_array($items[0]), 503, 'Apple transaction proof is missing or ambiguous.');
        return $this->validate($items[0], $reader, $transaction, $purchasedAt);
    }

    public function purchases(Reader $reader): array
    {
        $this->mapping();
        $identities = \Illuminate\Support\Facades\DB::table('apple_purchase_sessions')->where('reader_id', $reader->id)
            ->whereNull('revoked_at_ms')->where(fn ($q) => $q->whereNotNull('transaction_id')
                ->orWhere('expires_at_ms', '>=', now()->getTimestampMs()))->pluck('provider_identity')->all();
        if (\App\Models\Purchase::where('reader_id', $reader->id)->where('store', 'APP_STORE')
            ->whereNotIn('transaction_id', \Illuminate\Support\Facades\DB::table('apple_purchase_sessions')->whereNotNull('transaction_id')->select('transaction_id'))->exists()) {
            $identities[] = (string) $reader->id;
        }
        $items = [];
        foreach ($identities as $identity) {
            $items = array_merge($items, $this->list($this->prefix().'/customers/'.rawurlencode($identity).'/purchases', ['environment' => 'sandbox']));
        }
        $result = [];
        foreach ($items as $p) {
            abort_unless(is_array($p), 503);
            if (($p['product_id'] ?? null) !== config('purchases.apple_product_id')) { continue; }
            $result[] = $this->validate($p, $reader->id);
        }
        return $result;
    }
}
