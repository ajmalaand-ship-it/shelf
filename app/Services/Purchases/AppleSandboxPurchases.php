<?php

namespace App\Services\Purchases;

use App\Models\{AuthorShareAgreement, Collection, Purchase, PurchaseEvent, Reader, SalesLedger};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Shared Apple ledger, including the closed legacy owner sandbox path. Corrections append. */
class AppleSandboxPurchases
{
    public function __construct(private ApplePurchaseProof $proof) {}

    public static function active(Purchase $purchase): bool
    {
        if ($purchase->store !== 'APP_STORE') {
            return ! $purchase->entries()->whereIn('status', ['refund', 'revoke'])->exists();
        }
        $last = $purchase->entries()->whereIn('status', ['refund', 'revoke', 'refund_reversed'])->orderByDesc('id')->first();
        return $last ? $last->status === 'refund_reversed' : $purchase->entries()->where('status', 'sale')->exists();
    }

    public function receive(array $event, bool $restObservation = false): void
    {
        $existing = Purchase::where('store', 'APP_STORE')->where('transaction_id', $event['transaction_id'])->first();
        $customer = ctype_digit((string) ($event['app_user_id'] ?? ''))
            && (ApplePurchaseProof::customersConfigured() || ($existing && ApplePurchaseProof::customerPurchase($existing)));
        $environment = $event['environment'] ?? '';
        $product = $event['product_id'] ?? '';
        $bookId = $customer && in_array($product, ApplePurchaseProof::PRODUCTS, true) ? (int) substr($product, 11) : 3;
        abort_unless(($customer ? ApplePurchaseProof::credentialsConfigured() : ApplePurchaseProof::configured()) && ($event['store'] ?? null) === 'APP_STORE'
            && ($event['app_id'] ?? null) === config('purchases.apple_app_id')
            && ($customer ? in_array($environment, ['SANDBOX', 'PRODUCTION'], true) && in_array($product, ApplePurchaseProof::PRODUCTS, true)
                : $environment === 'SANDBOX' && $product === 'shelf_book_3')
            && is_string($event['transaction_id'] ?? null) && preg_match('/^[0-9]{1,255}$/D', $event['transaction_id']), 422);
        $type = $event['type'];
        abort_unless(in_array($type, ['NON_RENEWING_PURCHASE', 'CANCELLATION', 'EXPIRATION', 'REFUND_REVERSED'], true), 422);
        abort_unless(! $customer || $existing || ApplePurchaseProof::customerCheckoutEnabled(), 503, 'Real purchases are not enabled.');
        $session = str_starts_with((string) $event['app_user_id'], 'shelf_apple_')
            ? ApplePurchaseSession::forIdentity($event['app_user_id']) : null;
        $readerId = $session ? (int) $session->reader_id : (int) $event['app_user_id'];
        $reader = Reader::find($readerId);
        $book = Collection::withTrashed()->whereKey($bookId)->where('product_id', $product)->firstOrFail();
        abort_unless($customer || $session || $existing, 409, 'Unbound Apple purchase.');
        if ($session) { ApplePurchaseSession::check($session, $readerId, $event['transaction_id'], $event['purchased_at_ms']); }
        abort_if($existing && ($existing->reader_id !== $readerId || $existing->collection_id !== $bookId
            || $existing->environment !== $environment), 409, 'Apple purchase belongs to another account.');
        if ($existing && ! $customer) { abort_unless($event['app_user_id'] === ApplePurchaseSession::identity($readerId, $event['transaction_id'], $event['purchased_at_ms']), 409); }
        abort_unless($reader ? ($customer ? ApplePurchaseProof::customerAllowed($reader, $existing === null && $type === 'NON_RENEWING_PURCHASE') : ApplePurchaseProof::owner($reader, $existing === null && $type === 'NON_RENEWING_PURCHASE')) : ($existing && in_array($type, ['CANCELLATION', 'EXPIRATION'], true)), 403);
        // Hash app/store/event into the immutable provider key; no shared Play event namespace.
        $id = 'apple:'.hash('sha256', $event['app_id'].'|'.$event['id']);
        ksort($event);
        $hash = hash('sha256', json_encode($event));
        $duplicate = PurchaseEvent::where('provider_event_id', $id)->first();
        if ($duplicate) {
            abort_unless($duplicate->store === 'APP_STORE' && $duplicate->app_id === $event['app_id']
                && ($duplicate->details['payload_hash'] ?? null) === $hash, 409);
            return;
        }
        $purchasedAt = CarbonImmutable::createFromTimestampMs($event['purchased_at_ms']);
        $occurredAt = CarbonImmutable::createFromTimestampMs($event['event_timestamp_ms']);
        $proof = $customer ? $this->proof->customerTransaction($readerId, $event['transaction_id'], $event['purchased_at_ms'], $product, $environment)
            : $this->proof->transaction($readerId, $event['transaction_id'], $event['purchased_at_ms']);
        abort_unless($proof['status'] === (in_array($type, ['CANCELLATION', 'EXPIRATION'], true) ? 'refunded' : 'owned'), 503, 'Apple proof has not confirmed this event.');
        DB::transaction(function () use ($event, $id, $hash, $reader, $book, $type, $purchasedAt, $occurredAt, $restObservation, $existing, $readerId, $session, $customer, $environment, $bookId, $product): void {
            // One book lock serializes transaction ownership and duplicate processing across readers.
            if ($reader) {
                $lockedReader = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
                abort_unless($customer ? ApplePurchaseProof::customerAllowed($lockedReader, ! $existing && $type === 'NON_RENEWING_PURCHASE')
                    : ApplePurchaseProof::owner($lockedReader, ! $existing && $type === 'NON_RENEWING_PURCHASE'), 403);
            }
            $book = Collection::withTrashed()->whereKey($bookId)->lockForUpdate()->firstOrFail();
            if ($session) {
                $session = DB::table('apple_purchase_sessions')->where('id', $session->id)->lockForUpdate()->first();
                ApplePurchaseSession::check($session, $readerId, $event['transaction_id'], $event['purchased_at_ms']);
                DB::table('apple_purchase_sessions')->where('id', $session->id)->update(['transaction_id' => $event['transaction_id'], 'store_purchased_at_ms' => $event['purchased_at_ms']]);
            }
            $duplicate = PurchaseEvent::where('provider_event_id', $id)->first();
            if ($duplicate) {
                abort_unless(($duplicate->details['payload_hash'] ?? null) === $hash, 409);
                return;
            }
            $purchase = Purchase::where('store', 'APP_STORE')->where('transaction_id', $event['transaction_id'])->lockForUpdate()->first();
            abort_if($purchase && ($purchase->reader_id !== $readerId || $purchase->collection_id !== $bookId
                || $purchase->environment !== $environment || $purchase->store_purchased_at_ms !== $event['purchased_at_ms']), 409);
            if ($type === 'NON_RENEWING_PURCHASE' && ! $purchase) {
                if (! $customer && $reader && app(\App\Services\BookAccessService::class)->ownsBook($lockedReader, $book)) {
                    abort_unless(OwnerAppleSandboxTest::allowed($lockedReader, $book), 409, 'Owned-book Apple test is not authorized.');
                    abort_unless(DB::table('purchase_consents')->where('reader_id', $reader->id)
                        ->where('collection_id', $bookId)->where('store', 'APP_STORE')
                        ->where('wording', OwnerAppleSandboxTest::CONSENT)
                        ->where('created_at', '<=', $purchasedAt)
                        ->where('created_at', '>=', $purchasedAt->subMinutes(15))->exists(), 409, 'Fresh owned-book Apple test agreement missing.');
                }
                abort_unless($reader && DB::table('purchase_consents')->where('reader_id', $reader->id)->where('collection_id', $bookId)->where('store', 'APP_STORE')
                    ->when($customer, fn ($q) => $q->where('wording', PurchaseService::CONSENT))
                    ->where('created_at', '<=', $purchasedAt->addSeconds(5))->exists(), 409, 'Purchase agreement missing.');
            }
            // Reversals cannot create an account, purchase or restore an unproven earlier refund.
            if ($type === 'REFUND_REVERSED') {
                $last = $purchase?->entries()->whereIn('status', ['refund', 'revoke', 'refund_reversed'])->orderByDesc('id')->first();
                abort_unless($last?->status === 'refund' && ! $purchase->entries()->where('status', 'revoke')->exists(), 409, 'A matching unreversed refund without revocation is required.');
            }
            $entry = PurchaseEvent::create(['provider_event_id' => $id, 'store' => 'APP_STORE', 'app_id' => $event['app_id'],
                'event_type' => $type, 'reader_id' => $readerId, 'collection_id' => $bookId,
                'transaction_id' => $event['transaction_id'], 'environment' => $environment, 'occurred_at' => $occurredAt, 'event_timestamp_ms' => $event['event_timestamp_ms'],
                'details' => ['payload_hash' => $hash, 'rest_observation' => $restObservation, 'customer_purchase' => $customer]]);
            if ($type === 'NON_RENEWING_PURCHASE') {
                $purchase ??= Purchase::create(['reader_id' => $reader->id, 'collection_id' => $bookId, 'store' => 'APP_STORE',
                    'environment' => $environment, 'transaction_id' => $event['transaction_id'], 'product_id' => $product, 'purchased_at' => $purchasedAt, 'store_purchased_at_ms' => $event['purchased_at_ms']]);
                $newSale = ! $purchase->entries()->where('status', 'sale')->exists();
                if ($newSale) {
                    $agreement = AuthorShareAgreement::where('collection_id', $bookId)->where('starts_at', '<=', $purchasedAt)->orderByDesc('starts_at')->orderByDesc('id')->first();
                    SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $entry->id, 'entry_key' => 'sale:'.$purchase->id,
                        'status' => 'sale', 'currency' => $event['currency'] ?? null, 'amount' => $event['price_in_purchased_currency'] ?? null,
                        'occurred_at' => $purchasedAt, 'agreement_snapshot' => $agreement?->toArray(), 'earnings_status' => $environment === 'SANDBOX' ? 'test' : 'unknown']);
                }
                // Preserve refund-before-sale tombstones, scoped to the exact Apple reader/transaction.
                foreach (($newSale ? PurchaseEvent::where('store', 'APP_STORE')->where('app_id', $event['app_id'])
                    ->where('reader_id', $reader->id)->where('transaction_id', $purchase->transaction_id)
                    ->where('environment', $environment)->whereIn('event_type', ['CANCELLATION', 'EXPIRATION'])->orderBy('event_timestamp_ms')->get() : []) as $refund) {
                    $this->correction($purchase, $refund->event_type === 'CANCELLATION' ? 'refund' : 'revoke', $refund, true);
                }
            } elseif ($purchase) {
                $this->correction($purchase, match ($type) { 'REFUND_REVERSED' => 'refund_reversed', 'CANCELLATION' => 'refund', default => 'revoke' }, $entry);
            }
            if ($reader) { app(PurchaseService::class)->refreshAccess($reader, $book); }
        }, 3);
    }

    private function correction(Purchase $purchase, string $status, PurchaseEvent $event, bool $historicalTombstone = false): void
    {
        $previous = $purchase->entries()->whereIn('status', ['refund', 'revoke', 'refund_reversed'])->orderByDesc('id')->first();
        // Provider chronology and REST observation time are different evidence. Never invent a refund date.
        if (! $historicalTombstone && ! ($event->details['rest_observation'] ?? false)) {
            $latestProvider = PurchaseEvent::where('store', 'APP_STORE')->where('app_id', $event->app_id)
                ->where('transaction_id', $event->transaction_id)->where('id', '!=', $event->id)
                ->whereIn('event_type', ['CANCELLATION', 'EXPIRATION', 'REFUND_REVERSED'])->get()
                ->reject(fn ($e) => $e->details['rest_observation'] ?? false)->max('event_timestamp_ms');
            abort_if($latestProvider !== null && $latestProvider >= $event->event_timestamp_ms
                && $previous?->status !== $status, 409, 'Out-of-order Apple correction needs fresh reconciliation.');
        }
        // Duplicate state notifications cannot multiply monetary corrections.
        $financialLast = $purchase->entries()->whereIn('status', ['refund', 'refund_reversed'])->orderByDesc('id')->first();
        if ($status === 'refund' && $financialLast?->status === 'refund') { return; }
        if ($previous?->status === $status) { return; }
        $key = 'apple:'.$status.':'.$purchase->id.':'.$event->id;
        if ($purchase->entries()->where('entry_key', $key)->exists()) { return; }
        $sale = $purchase->entries()->where('status', 'sale')->first();
        SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $event->id, 'entry_key' => $key,
            'status' => $status, 'currency' => $sale?->currency,
            'amount' => $status === 'refund' && $sale?->amount !== null ? '-'.$sale->amount : ($status === 'refund_reversed' ? $sale?->amount : null),
            'occurred_at' => $event->occurred_at, 'agreement_snapshot' => $sale?->agreement_snapshot, 'earnings_status' => $purchase->environment === 'SANDBOX' ? 'test' : 'unknown']);
    }

    public function reconcile(Reader $reader): void
    {
        $recorded = Purchase::where('store', 'APP_STORE')->where('reader_id', $reader->id)->get();
        $customerRecorded = $recorded->filter(fn ($p) => ApplePurchaseProof::customerPurchase($p));
        if (ApplePurchaseProof::customersConfigured() || $customerRecorded->isNotEmpty()) {
            abort_unless(ApplePurchaseProof::credentialsConfigured() && ApplePurchaseProof::customerAllowed($reader, false), 503);
            foreach ($this->proof->customerPurchases($reader) as $p) {
                $purchase = $customerRecorded->firstWhere('transaction_id', $p['store_purchase_identifier']);
                if ($p['status'] === 'refunded') {
                    if ($purchase && self::active($purchase)) { $this->receive($this->customerRestEvent($p, 'CANCELLATION'), true); }
                } elseif (! $purchase && ApplePurchaseProof::customerCheckoutEnabled()) {
                    $this->receive($this->customerRestEvent($p, 'NON_RENEWING_PURCHASE'), true);
                }
            }
            foreach ($customerRecorded as $purchase) {
                if (! self::active($purchase)) { continue; }
                $p = $this->proof->customerTransaction($reader->id, $purchase->transaction_id, $purchase->store_purchased_at_ms, $purchase->product_id, $purchase->environment);
                if ($p['status'] === 'refunded') { $this->receive($this->customerRestEvent($p, 'CANCELLATION'), true); }
            }
        }
        $recorded = $recorded->reject(fn ($p) => ApplePurchaseProof::customerPurchase($p));
        if (! ApplePurchaseProof::configured()) {
            // Access filtering excludes Apple-only content while Play verification continues.
            return;
        }
        if (! ApplePurchaseProof::owner($reader, false)) { abort_if($recorded->isNotEmpty(), 403); return; }
        foreach ($this->proof->purchases($reader) as $p) {
            $purchase = $recorded->firstWhere('transaction_id', $p['store_purchase_identifier']);
            if ($p['status'] === 'refunded') {
                if ($purchase && self::active($purchase)) {
                    $this->receive($this->restEvent($reader, $p, 'CANCELLATION'), true);
                }
                continue;
            }
            if ($purchase) { continue; } // Existing proof is checked; only REFUND_REVERSED restores a refunded transaction.
            $this->receive($this->restEvent($reader, $p, 'NON_RENEWING_PURCHASE'), true);
        }
        // Independently re-check every active recorded transaction; absence is unknown, never a fabricated revoke.
        foreach ($recorded as $purchase) {
            if (! self::active($purchase)) { continue; }
            $p = $this->proof->transaction($reader->id, $purchase->transaction_id, $purchase->store_purchased_at_ms);
            if ($p['status'] === 'refunded') { $this->receive($this->restEvent($reader, $p, 'CANCELLATION'), true); }
        }
    }

    private function customerRestEvent(array $p, string $type): array
    {
        return ['id' => 'rest:'.$type.':'.$p['id'].':'.($type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()),
            'type' => $type, 'store' => 'APP_STORE', 'app_id' => config('purchases.apple_app_id'), 'environment' => strtoupper($p['environment']),
            'product_id' => $p['shelf_product_id'], 'app_user_id' => $p['customer_id'], 'transaction_id' => $p['store_purchase_identifier'],
            'purchased_at_ms' => $p['purchased_at'], 'event_timestamp_ms' => $type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()];
    }

    private function restEvent(Reader $reader, array $p, string $type): array
    {
        return ['id' => 'rest:'.$type.':'.$p['id'].':'.($type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()),
            'type' => $type, 'store' => 'APP_STORE', 'app_id' => config('purchases.apple_app_id'), 'environment' => 'SANDBOX',
            'product_id' => 'shelf_book_3', 'app_user_id' => $p['customer_id'], 'transaction_id' => $p['store_purchase_identifier'],
            'purchased_at_ms' => $p['purchased_at'], 'event_timestamp_ms' => $type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()];
    }
}
