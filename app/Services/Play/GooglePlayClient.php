<?php

namespace App\Services\Play;

use App\Models\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GooglePlayClient
{
    private const OAUTH = 'https://oauth2.googleapis.com/token';

    private const SCOPE = 'https://www.googleapis.com/auth/androidpublisher';

    private ?string $token = null;

    public static function configured(): bool
    {
        return ! config('play_sync.deferred', true) && ! \App\Support\Staging::active() && (bool) config('play_sync.enabled') && filled(config('play_sync.credentials_path'));
    }

    private function token(): string
    {
        if (\App\Support\Staging::active()) {
            throw new PlaySyncException('Google Play price sync is permanently disabled on the test copy.');
        }
        if ($this->token) {
            return $this->token;
        }
        $path = config('play_sync.credentials_path');
        $real = is_string($path) ? realpath($path) : false;
        if (! $real || $real !== $path || ! is_file($real) || ! is_readable($real)
            || ! str_starts_with($real, '/home/shelf/') || str_starts_with($real, base_path().'/')
            || str_starts_with($real, '/home/shelf/public_html/') || (fileperms($real) & 0077) !== 0) {
            throw new PlaySyncException('Google Play credentials need a private server file readable only by Shelf.');
        }
        try {
            $key = json_decode(file_get_contents($real), true, 16, JSON_THROW_ON_ERROR);
            if (($key['type'] ?? null) !== 'service_account' || ! filter_var($key['client_email'] ?? '', FILTER_VALIDATE_EMAIL)
                || ! is_string($key['private_key'] ?? null)) {
                throw new \RuntimeException;
            }
            $encode = fn (string $v): string => rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
            $head = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = $encode(json_encode(['iss' => $key['client_email'], 'scope' => self::SCOPE,
                'aud' => self::OAUTH, 'iat' => time(), 'exp' => time() + 3600]));
            $private = openssl_pkey_get_private($key['private_key']);
            if (! $private || ! openssl_sign($head.'.'.$claim, $signature, $private, OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException;
            }
            $assertion = $head.'.'.$claim.'.'.$encode($signature);
        } catch (\Throwable) {
            throw new PlaySyncException('Google Play credentials could not be read. Check the service-account file.');
        }
        $response = Http::asForm()->acceptJson()->withoutRedirecting()->connectTimeout(3)->timeout(8)
            ->post(self::OAUTH, ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $assertion]);
        $this->check($response);
        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new PlaySyncException('Google sign-in for price sync is unavailable. It will retry.');
        }

        return $this->token = $token;
    }

    private function check(Response $response, bool $missingAllowed = false): void
    {
        if ($response->successful() || ($missingAllowed && $response->status() === 404)) {
            return;
        }
        throw new PlaySyncException(match ($response->status()) {
            401, 403 => 'Google Play denied access. Check the service account and its Play permissions.',
            404 => 'The Play app or product is not ready. Check the app setup; Shelf will retry.',
            400, 409 => 'Google Play could not accept this product or price. Check the Play app setup and price; Shelf will retry.',
            429 => 'Google Play is busy. Shelf will retry automatically.',
            default => 'Google Play is temporarily unavailable. Shelf will retry automatically.',
        }, $response->status());
    }

    private function request(string $method, string $path, array $body = [], array $query = [], bool $missingAllowed = false): Response
    {
        $base = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/'.rawurlencode(config('play_sync.package'));
        $options = ['query' => $query];
        if ($method !== 'GET') {
            $options['json'] = $body;
        }
        $response = Http::withToken($this->token())->acceptJson()->withoutRedirecting()->connectTimeout(3)->timeout(8)
            ->send($method, $base.$path, $options);
        $this->check($response, $missingAllowed);

        return $response;
    }

    public function orderPurchaseToken(\App\Models\Purchase $purchase): string
    {
        if (\App\Support\Staging::active() || config('play_sync.package') !== 'services.shelf.app') {
            throw new PlaySyncException('Purchase recovery is forbidden on another package or test copy.');
        }
        $order = $this->request('GET', '/orders/'.rawurlencode($purchase->transaction_id))->json();
        if (! is_array($order) || ($order['orderId'] ?? null) !== $purchase->transaction_id
            || ($order['state'] ?? null) !== 'PROCESSED' || count($order['lineItems'] ?? []) !== 1
            || ($order['lineItems'][0]['productId'] ?? null) !== $purchase->product_id
            || ! is_string($order['purchaseToken'] ?? null) || blank($order['purchaseToken'])) {
            throw new PlaySyncException('Store order is unavailable or not eligible for recovery.');
        }
        return $order['purchaseToken']; // Memory only; never log or persist buyer metadata/token.
    }

    public function productPurchase(string $token): array
    {
        if (\App\Support\Staging::active() || config('play_sync.package') !== 'services.shelf.app') {
            throw new PlaySyncException('Production purchase recovery is forbidden on another package or test copy.');
        }
        $result = $this->request('GET', '/purchases/productsv2/tokens/'.rawurlencode($token))->json();
        if (! is_array($result)) { throw new PlaySyncException('Purchase verification unavailable.'); }
        return $result;
    }

    public function voidedPurchases(?string $pageToken = null): Response
    {
        if (\App\Support\Staging::active() || config('play_sync.package') !== 'services.shelf.app') {
            throw new PlaySyncException('Production refund polling is forbidden on the test copy or another package.');
        }
        return $this->request('GET', '/purchases/voidedpurchases', query: array_filter([
            'type' => 0, 'maxResults' => 1000, 'token' => $pageToken,
        ], fn ($value) => $value !== null));
    }

    public function sync(Collection $book): void
    {
        if (config('play_sync.deferred', true)) {
            throw new PlaySyncException('Automatic sync is deferred. Manage products and prices in Play Console.');
        }
        if ($book->product_id !== 'shelf_book_'.$book->id) {
            throw new PlaySyncException('This book needs its permanent Shelf product ID.');
        }
        $path = '/onetimeproducts/'.rawurlencode($book->product_id);
        // Google uses different path casing for reads and product upserts.
        $existing = $this->request('GET', '/oneTimeProducts/'.rawurlencode($book->product_id), missingAllowed: true);
        $old = $existing->successful() ? $existing->json() : null;
        $options = $old['purchaseOptions'] ?? [];
        if ($old && (count($options) !== 1 || ($options[0]['purchaseOptionId'] ?? '') !== 'buy'
            || ! isset($options[0]['buyOption']))) {
            throw new PlaySyncException('This product has unexpected purchase options. Support must check it before syncing.');
        }
        $active = $book->isPublished() && $book->price_usd !== null;
        // No price: deactivate an existing product; never invent a price to create one.
        if ($book->price_usd === null) {
            if ($old && ($options[0]['state'] ?? '') === 'ACTIVE') {
                $this->state($book, false);
            }

            return;
        }
        [$units, $cents] = explode('.', $book->price_usd);
        $conversion = $this->request('POST', '/pricing:convertRegionPrices', [
            'price' => ['currencyCode' => 'USD', 'units' => $units, 'nanos' => (int) $cents * 10000000],
        ])->json();
        $regions = [];
        foreach ($conversion['convertedRegionPrices'] ?? [] as $region => $price) {
            if (! isset($price['price']['currencyCode']) || ! is_array($price['price'])) {
                throw new PlaySyncException('Google returned incomplete local prices. Shelf will retry.');
            }
            $regions[] = ['regionCode' => $region, 'price' => $price['price'], 'availability' => 'AVAILABLE'];
        }
        if (! $regions || empty($conversion['regionVersion']['version'])
            || ! isset($conversion['convertedOtherRegionsPrice']['usdPrice'], $conversion['convertedOtherRegionsPrice']['eurPrice'])) {
            throw new PlaySyncException('Google returned incomplete local prices. Shelf will retry.');
        }
        $option = ['purchaseOptionId' => 'buy', 'buyOption' => ['legacyCompatible' => true, 'multiQuantityEnabled' => false],
            'regionalPricingAndAvailabilityConfigs' => $regions,
            'newRegionsConfig' => array_merge($conversion['convertedOtherRegionsPrice'], ['availability' => 'AVAILABLE'])];
        $saved = $this->request('PATCH', $path, ['packageName' => config('play_sync.package'), 'productId' => $book->product_id,
            // This is a short store listing only; the source title and text are never modified.
            'listings' => [['languageCode' => 'en-US', 'title' => mb_substr($book->title, 0, 55),
                'description' => 'Full book in your Shelf Library.']], 'purchaseOptions' => [$option]], [
                    'updateMask' => 'listings,purchaseOptions', 'allowMissing' => 'true',
                    'regionsVersion.version' => $conversion['regionVersion']['version'],
                ])->json();
        if (($saved['productId'] ?? '') !== $book->product_id || ($saved['purchaseOptions'][0]['purchaseOptionId'] ?? '') !== 'buy') {
            throw new PlaySyncException('Google has not confirmed the product update. Shelf will retry.');
        }
        $state = $saved['purchaseOptions'][0]['state'] ?? 'DRAFT';
        if ($active && $state !== 'ACTIVE') {
            $this->state($book, true);
        }
        if (! $active && $state === 'ACTIVE') {
            $this->state($book, false);
        }
    }

    private function state(Collection $book, bool $active): void
    {
        $response = $this->request('POST', '/oneTimeProducts/'.rawurlencode($book->product_id).'/purchaseOptions:batchUpdateStates', [
            'requests' => [[$active ? 'activatePurchaseOptionRequest' : 'deactivatePurchaseOptionRequest' => [
                'packageName' => config('play_sync.package'), 'productId' => $book->product_id, 'purchaseOptionId' => 'buy',
            ]]],
        ]);
        $product = $response->json('oneTimeProducts.0');
        if (($product['productId'] ?? '') !== $book->product_id
            || ($product['purchaseOptions'][0]['state'] ?? '') !== ($active ? 'ACTIVE' : 'INACTIVE')) {
            throw new PlaySyncException('Google has not confirmed availability. Shelf will retry.');
        }
    }
}
