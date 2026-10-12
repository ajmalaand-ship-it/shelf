<?php

namespace App\Services\Purchases;

use App\Models\{AuthorShareAgreement, Collection, Purchase, PurchaseEvent, Reader, SalesLedger};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Sandbox only. Apple ledger corrections never overwrite the original sale/refund. */
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
        abort_unless(ApplePurchaseProof::configured() && ($event['store'] ?? null) === 'APP_STORE'
            && ($event['app_id'] ?? null) === config('purchases.apple_app_id')
            && ($event['environment'] ?? null) === 'SANDBOX' && ($event['product_id'] ?? null) === 'shelf_book_3'
            && is_string($event['transaction_id'] ?? null) && preg_match('/^[0-9]{1,255}$/D', $event['transaction_id']), 422);
        $type = $event['type'];
        abort_unless(in_array($type, ['NON_RENEWING_PURCHASE', 'CANCELLATION', 'EXPIRATION', 'REFUND_REVERSED'], true), 422);
        $reader = Reader::find($event['app_user_id']);
        $book = Collection::withTrashed()->whereKey(3)->where('product_id', 'shelf_book_3')->firstOrFail();
        $existing = Purchase::where('store', 'APP_STORE')->where('transaction_id', $event['transaction_id'])->first();
        abort_if($existing && ($existing->reader_id !== (int) $event['app_user_id'] || $existing->collection_id !== 3
            || $existing->environment !== 'SANDBOX'), 409, 'Apple purchase belongs to another account.');
        abort_unless($reader ? ApplePurchaseProof::owner($reader, $existing === null && $type === 'NON_RENEWING_PURCHASE') : ($existing && in_array($type, ['CANCELLATION', 'EXPIRATION'], true)), 403);
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
        $proof = $this->proof->transaction((int) $event['app_user_id'], $event['transaction_id'], $event['purchased_at_ms']);
        abort_unless($proof['status'] === (in_array($type, ['CANCELLATION', 'EXPIRATION'], true) ? 'refunded' : 'owned'), 503, 'Apple proof has not confirmed this event.');
        DB::transaction(function () use ($event, $id, $hash, $reader, $book, $type, $purchasedAt, $occurredAt, $restObservation, $existing): void {
            // One book lock serializes transaction ownership and duplicate processing across readers.
            if ($reader) {
                $lockedReader = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
                abort_unless(ApplePurchaseProof::owner($lockedReader, ! $existing && $type === 'NON_RENEWING_PURCHASE'), 403);
            }
            Collection::withTrashed()->whereKey(3)->lockForUpdate()->firstOrFail();
            $duplicate = PurchaseEvent::where('provider_event_id', $id)->first();
            if ($duplicate) {
                abort_unless(($duplicate->details['payload_hash'] ?? null) === $hash, 409);
                return;
            }
            $purchase = Purchase::where('store', 'APP_STORE')->where('transaction_id', $event['transaction_id'])->lockForUpdate()->first();
            abort_if($purchase && ($purchase->reader_id !== (int) $event['app_user_id'] || $purchase->collection_id !== 3
                || $purchase->environment !== 'SANDBOX' || $purchase->store_purchased_at_ms !== $event['purchased_at_ms']), 409);
            if ($type === 'NON_RENEWING_PURCHASE' && ! $purchase) {
                abort_unless($reader && DB::table('purchase_consents')->where('reader_id', $reader->id)->where('collection_id', 3)->where('store', 'APP_STORE')
                    ->where('created_at', '<=', $purchasedAt->addSeconds(5))->exists(), 409, 'Purchase agreement missing.');
            }
            // Reversals cannot create an account, purchase or restore an unproven earlier refund.
            if ($type === 'REFUND_REVERSED') {
                $last = $purchase?->entries()->whereIn('status', ['refund', 'revoke', 'refund_reversed'])->orderByDesc('id')->first();
                abort_unless($last?->status === 'refund' && ! $purchase->entries()->where('status', 'revoke')->exists(), 409, 'A matching unreversed refund without revocation is required.');
            }
            $entry = PurchaseEvent::create(['provider_event_id' => $id, 'store' => 'APP_STORE', 'app_id' => $event['app_id'],
                'event_type' => $type, 'reader_id' => (int) $event['app_user_id'], 'collection_id' => 3,
                'transaction_id' => $event['transaction_id'], 'environment' => 'SANDBOX', 'occurred_at' => $occurredAt, 'event_timestamp_ms' => $event['event_timestamp_ms'],
                'details' => ['payload_hash' => $hash, 'rest_observation' => $restObservation]]);
            if ($type === 'NON_RENEWING_PURCHASE') {
                $purchase ??= Purchase::create(['reader_id' => $reader->id, 'collection_id' => 3, 'store' => 'APP_STORE',
                    'environment' => 'SANDBOX', 'transaction_id' => $event['transaction_id'], 'product_id' => 'shelf_book_3', 'purchased_at' => $purchasedAt, 'store_purchased_at_ms' => $event['purchased_at_ms']]);
                $newSale = ! $purchase->entries()->where('status', 'sale')->exists();
                if ($newSale) {
                    $agreement = AuthorShareAgreement::where('collection_id', 3)->where('starts_at', '<=', $purchasedAt)->orderByDesc('starts_at')->first();
                    SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $entry->id, 'entry_key' => 'sale:'.$purchase->id,
                        'status' => 'sale', 'currency' => $event['currency'] ?? null, 'amount' => $event['price_in_purchased_currency'] ?? null,
                        'occurred_at' => $purchasedAt, 'agreement_snapshot' => $agreement?->toArray(), 'earnings_status' => 'test']);
                }
                // Preserve refund-before-sale tombstones, scoped to the exact Apple reader/transaction.
                foreach (($newSale ? PurchaseEvent::where('store', 'APP_STORE')->where('app_id', $event['app_id'])
                    ->where('reader_id', $reader->id)->where('transaction_id', $purchase->transaction_id)
                    ->where('environment', 'SANDBOX')->whereIn('event_type', ['CANCELLATION', 'EXPIRATION'])->orderBy('event_timestamp_ms')->get() : []) as $refund) {
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
            'occurred_at' => $event->occurred_at, 'agreement_snapshot' => $sale?->agreement_snapshot, 'earnings_status' => 'test']);
    }

    public function reconcile(Reader $reader): void
    {
        $recorded = Purchase::where('store', 'APP_STORE')->where('reader_id', $reader->id)->get();
        if (! ApplePurchaseProof::configured()) {
            // Disabled configuration cannot issue a fresh offline lease for Apple-owned content.
            abort_if($recorded->isNotEmpty(), 503, 'Apple store verification is unavailable.');
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

    private function restEvent(Reader $reader, array $p, string $type): array
    {
        return ['id' => 'rest:'.$type.':'.$p['id'].':'.($type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()),
            'type' => $type, 'store' => 'APP_STORE', 'app_id' => config('purchases.apple_app_id'), 'environment' => 'SANDBOX',
            'product_id' => 'shelf_book_3', 'app_user_id' => (string) $reader->id, 'transaction_id' => $p['store_purchase_identifier'],
            'purchased_at_ms' => $p['purchased_at'], 'event_timestamp_ms' => $type === 'NON_RENEWING_PURCHASE' ? $p['purchased_at'] : now()->getTimestampMs()];
    }
}
