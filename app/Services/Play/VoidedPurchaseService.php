<?php

namespace App\Services\Play;

use App\Models\{PlayPurchaseReference, PlayRefundRun, Purchase};
use App\Services\Purchases\PurchaseService;
use App\Support\Staging;
use Illuminate\Support\Facades\{DB, Log};

class VoidedPurchaseService
{
    public function __construct(private GooglePlayClient $google, private PurchaseService $purchases) {}

    public function run(): array
    {
        if (Staging::active() || ! config('play_refunds.enabled') || config('play_sync.package') !== 'services.shelf.app') {
            throw new PlaySyncException('Production refund polling is disabled here.');
        }
        $started = now();
        $deadline = microtime(true) + 830;
        $result = ['status' => 'completed', 'http_status' => null, 'pages' => 0, 'seen' => 0,
            'refunded' => 0, 'duplicate' => 0, 'unmatched' => 0, 'conflict' => 0, 'error_code' => null];
        $token = null;
        $visited = [];
        try {
            do {
                if (microtime(true) >= $deadline) { throw new PlaySyncException('Refund check reached its time limit.'); }
                $response = $this->google->voidedPurchases($token);
                $result['http_status'] = $response->status();
                $body = $response->json();
                if (! is_array($body) || isset($body['error']) || (isset($body['voidedPurchases']) && ! is_array($body['voidedPurchases']))) {
                    throw new PlaySyncException('Malformed Google refund response.');
                }
                $rows = $body['voidedPurchases'] ?? [];
                // Validate the entire page before any change from this page.
                foreach ($rows as $row) { $this->validate($row); }
                $next = $body['tokenPagination']['nextPageToken'] ?? null;
                if ($next !== null && (! is_string($next) || $next === '' || strlen($next) > 8192 || isset($visited[hash('sha256', $next)]))) {
                    throw new PlaySyncException('Invalid Google refund pagination.');
                }
                foreach ($rows as $row) {
                    $outcome = $this->apply($row);
                    $result[$outcome]++;
                    $result['seen']++;
                }
                $result['pages']++;
                $token = $next;
                if ($token !== null) {
                    $visited[hash('sha256', $token)] = true;
                    // Stay below Google's 30 requests / 30 seconds per package.
                    if (! app()->runningUnitTests()) { usleep(1100000); }
                }
            } while ($token !== null);
            if ($result['conflict'] > 0) {
                $result['status'] = 'warning';
                $result['error_code'] = 'ambiguous_match';
            }
        } catch (\Throwable $error) {
            $result['status'] = 'failed';
            $result['http_status'] = in_array($error->getCode(), [400, 401, 403, 404, 409, 429, 500, 502, 503, 504], true)
                ? $error->getCode() : $result['http_status'];
            // Never log provider bodies, bearer tokens, purchase tokens or exception messages.
            $result['error_code'] = $error instanceof PlaySyncException ? 'google_or_validation_error'
                : ($error instanceof \Illuminate\Http\Client\ConnectionException ? 'google_connection_error' : 'local_processing_error');
        }
        PlayRefundRun::create($result + ['started_at' => $started, 'finished_at' => now()]);
        Log::log($result['status'] === 'completed' ? 'info' : 'warning', 'Shelf Google refund check', $result);
        return $result;
    }

    private function validate(mixed $row): void
    {
        if (! is_array($row) || ! is_string($row['purchaseToken'] ?? null) || $row['purchaseToken'] === ''
            || strlen($row['purchaseToken']) > 8192
            || (isset($row['orderId']) && (! is_string($row['orderId']) || strlen($row['orderId']) > 255))
            || isset($row['voidedQuantity'])) {
            throw new PlaySyncException('Invalid or unsupported Google voided purchase.');
        }
        foreach (['purchaseTimeMillis', 'voidedTimeMillis'] as $field) {
            if (! preg_match('/^[1-9][0-9]{0,12}$/', (string) ($row[$field] ?? ''))
                || (int) $row[$field] > now()->addMinutes(5)->getTimestampMs()) {
                throw new PlaySyncException('Invalid Google voided purchase timestamp.');
            }
        }
        if ((int) $row['voidedTimeMillis'] < (int) $row['purchaseTimeMillis']) {
            throw new PlaySyncException('Invalid Google voided purchase order.');
        }
        foreach (['voidedSource', 'voidedReason'] as $field) {
            if (isset($row[$field]) && (! is_int($row[$field]) || $row[$field] < 0 || $row[$field] > 100)) {
                throw new PlaySyncException('Invalid Google voided purchase reason.');
            }
        }
    }

    public function apply(array $row): string
    {
        $this->validate($row);
        return DB::transaction(function () use ($row): string {
            $hash = hash('sha256', $row['purchaseToken']);
            $reference = PlayPurchaseReference::where('purchase_token_hash', $hash)->first();
            $order = $row['orderId'] ?? null;
            $query = Purchase::where('store', 'PLAY_STORE')->whereIn('environment', ['SANDBOX', 'PRODUCTION']);
            $orders = filled($order) ? (clone $query)->where('transaction_id', $order)->get() : collect();
            if ($orders->count() > 1) { return 'conflict'; }
            $purchase = $orders->first() ?? ($reference ? (clone $query)->find($reference->purchase_id) : null);
            if (! $purchase) { return 'unmatched'; }
            if (($reference && $reference->purchase_id !== $purchase->id)
                || (filled($order) && $purchase->transaction_id !== $order)
                || $purchase->product_id !== 'shelf_book_'.$purchase->collection_id
                || abs($purchase->purchased_at->getTimestampMs() - (int) $row['purchaseTimeMillis']) > 1000) {
                return 'conflict';
            }
            $known = PlayPurchaseReference::where('purchase_id', $purchase->id)->first();
            if ($known && $known->purchase_token_hash !== $hash) { return 'conflict'; }
            if (! $known) {
                PlayPurchaseReference::create(['purchase_id' => $purchase->id, 'purchase_token_hash' => $hash]);
            }
            return $this->purchases->recordGoogleRefund($purchase, $row, $orders->isEmpty() ? 'purchase_token_hash' : 'order_id')
                ? 'refunded' : 'duplicate';
        }, 3);
    }
}
