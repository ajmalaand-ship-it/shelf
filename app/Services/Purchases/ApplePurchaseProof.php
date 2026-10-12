<?php

namespace App\Services\Purchases;

use App\Models\Reader;
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

    public static function owner(Reader $reader, bool $checkout = true): bool
    {
        if (! $reader->email_verified_at || ($checkout && $reader->buying_blocked)) { return false; }
        if (app()->runningUnitTests() && in_array($reader->id, config('purchases.test_reader_ids', []), true)) { return true; }
        return \App\Models\User::where('is_owner', true)->whereRaw('LOWER(email) = ?', [strtolower($reader->email)])->exists();
    }

    private function get(string $path, array $query = []): array
    {
        abort_unless(self::configured(), 503, 'Apple sandbox is not configured.');
        try {
            $r = Http::withToken(RevenueCatClient::appleCredential())->acceptJson()->withoutRedirecting()
                ->connectTimeout(5)->timeout(12)->get('https://api.revenuecat.com'.$path, $query);
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
            && ($p['customer_id'] ?? null) === (string) $reader && ($p['original_customer_id'] ?? null) === (string) $reader
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
        $items = $this->list($this->prefix().'/customers/'.rawurlencode((string) $reader->id).'/purchases', ['environment' => 'sandbox']);
        $result = [];
        foreach ($items as $p) {
            abort_unless(is_array($p), 503);
            if (($p['product_id'] ?? null) !== config('purchases.apple_product_id')) { continue; }
            $result[] = $this->validate($p, $reader->id);
        }
        return $result;
    }
}
