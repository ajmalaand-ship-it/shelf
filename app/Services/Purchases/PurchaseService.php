<?php

namespace App\Services\Purchases;

use App\Models\{AuthorShareAgreement, Collection, Purchase, PurchaseEvent, Reader, SalesLedger};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public const CONSENT = 'Read the free sample first. All sales are final. I agree.';
    public function __construct(private RevenueCatClient $provider) {}

    public function receive(array $event): void
    {
        $event['app_user_id'] = \App\Support\Staging::readerId((string) $event['app_user_id']);
        $environment = $event['environment'] ?? '';
        abort_unless(in_array($environment, ['SANDBOX', 'PRODUCTION'], true) && ($event['store'] ?? '') === 'PLAY_STORE'
            && ($event['app_id'] ?? '') === config('purchases.app_id'), 422, 'Only verified Shelf Google Play events are accepted.');
        $type = $event['type'];
        if ($type === 'TEST') { return; }
        abort_unless(in_array($type, ['NON_RENEWING_PURCHASE', 'CANCELLATION', 'EXPIRATION'], true), 422, 'Unsupported event; no access granted.');
        $book = Collection::withTrashed()->where('product_id', $event['product_id'])->firstOrFail();
        abort_unless($book->product_id === 'shelf_book_'.$book->id, 422);
        $existing = Purchase::where('store', 'PLAY_STORE')->where('transaction_id', $event['transaction_id'])->first();
        abort_if($existing && ($existing->environment !== $environment
            || $existing->reader_id !== (int) $event['app_user_id'] || $existing->collection_id !== $book->id),
            409, 'Purchase belongs to another account, book or environment.');
        // A disabled sales gate must still accept refunds/revocations of prior sales.
        abort_unless($environment === 'SANDBOX' || RevenueCatClient::productionEnabled()
            || ($existing && $type !== 'NON_RENEWING_PURCHASE'), 422, 'Real purchases are disabled pending owner launch approval.');
        $reader = Reader::find($event['app_user_id']);
        if (! $reader) {
            // Deleting a login never deletes the financial trail or prevents later refunds.
            $historical = Purchase::where('store', 'PLAY_STORE')->where('environment', $environment)
                ->where('transaction_id', $event['transaction_id'])->where('collection_id', $book->id)
                ->where('reader_id', (int) $event['app_user_id'])->first();
            abort_unless($type !== 'NON_RENEWING_PURCHASE' && $historical, 404);
        }
        $duplicate = PurchaseEvent::where('provider_event_id', $event['id'])->first();
        if ($duplicate) {
            abort_unless($duplicate->reader_id === (int) $event['app_user_id'] && $duplicate->collection_id === $book->id
                && $duplicate->environment === $environment && $duplicate->transaction_id === $event['transaction_id']
                && $duplicate->event_type === $type, 409, 'Provider event conflicts with recorded history.');
            return;
        }
        $purchasedAt = CarbonImmutable::createFromTimestampMs($event['purchased_at_ms']);
        $occurredAt = CarbonImmutable::createFromTimestampMs($event['event_timestamp_ms']);
        $subscriber = $type === 'NON_RENEWING_PURCHASE' ? $this->provider->subscriber($reader) : null;
        DB::transaction(function () use ($event, $type, $book, $reader, $purchasedAt, $occurredAt, $subscriber, $environment): void {
            if ($reader) { Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail(); }
            if (PurchaseEvent::where('provider_event_id', $event['id'])->exists()) { return; }
            $purchase = Purchase::where('store', 'PLAY_STORE')->where('environment', $environment)->where('transaction_id', $event['transaction_id'])->lockForUpdate()->first();
            abort_if($purchase && ($purchase->reader_id !== (int) $event['app_user_id'] || $purchase->collection_id !== $book->id), 409, 'Purchase belongs to another account or book.');
            if ($type === 'NON_RENEWING_PURCHASE') {
                // Confirmation may arrive after withdrawal; previously initiated purchases remain valid.
                abort_unless($this->provider->confirms($subscriber, $book, $purchasedAt, $environment), 503, 'Confirming your purchase. Please try again.');
                if (! $purchase) {
                    abort_unless(DB::table('purchase_consents')->where('reader_id', $reader->id)->where('collection_id', $book->id)
                        ->where('created_at', '<=', $purchasedAt->addSeconds(5))->exists(), 409, 'Purchase agreement is missing. Contact Shelf support.');
                }
            }
            $entry = PurchaseEvent::create([
                'provider_event_id' => $event['id'], 'event_type' => $type, 'reader_id' => (int) $event['app_user_id'],
                'collection_id' => $book->id, 'transaction_id' => $event['transaction_id'], 'environment' => $environment,
                'occurred_at' => $occurredAt, 'details' => ['currency' => $event['currency'] ?? null,
                    'amount' => $event['price_in_purchased_currency'] ?? null, 'cancel_reason' => $event['cancel_reason'] ?? null],
            ]);
            if ($type === 'NON_RENEWING_PURCHASE') {
                $purchase ??= Purchase::create(['reader_id' => $reader->id, 'collection_id' => $book->id, 'store' => 'PLAY_STORE',
                    'environment' => $environment, 'transaction_id' => $event['transaction_id'], 'product_id' => $book->product_id, 'purchased_at' => $purchasedAt]);
                if (! SalesLedger::withTestPurchases()->where('entry_key', 'sale:'.$purchase->id)->exists()) {
                    $agreement = AuthorShareAgreement::where('collection_id', $book->id)->where('starts_at', '<=', $purchasedAt)->orderByDesc('starts_at')->orderByDesc('id')->first();
                    $amount = $event['price_in_purchased_currency'] ?? null;
                    // Test earns nothing; unknown real net/fees/taxes are not invented.
                    $estimates = null;
                    SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $entry->id, 'entry_key' => 'sale:'.$purchase->id,
                        'status' => 'sale', 'currency' => $event['currency'] ?? null, 'amount' => $amount,
                        'occurred_at' => $purchasedAt, 'agreement_snapshot' => $agreement?->toArray(),
                        'estimated_earnings' => $estimates, 'earnings_status' => $environment === 'SANDBOX' ? 'test' : 'unknown']);
                }
                // A refund received before the sale is a tombstone, not permission to unlock.
                foreach (PurchaseEvent::where('transaction_id', $purchase->transaction_id)->where('collection_id', $book->id)
                    ->where('reader_id', $reader->id)->where('environment', $environment)
                    ->whereIn('event_type', ['CANCELLATION', 'EXPIRATION'])->get() as $reversal) {
                    $this->reverse($purchase, $reversal->event_type === 'CANCELLATION' ? 'refund' : 'revoke', $reversal);
                }
            } elseif ($purchase) {
                $this->reverse($purchase, $type === 'CANCELLATION' ? 'refund' : 'revoke', $entry);
            }
            if ($reader) { $this->refreshAccess($reader, $book); }
        }, 3);
    }

    public function recordGoogleRefund(Purchase $purchase, array $void, string $matchedBy): bool
    {
        return DB::transaction(function () use ($purchase, $void, $matchedBy): bool {
            $reader = $purchase->reader_id ? Reader::whereKey($purchase->reader_id)->lockForUpdate()->first() : null;
            $purchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            $eventId = 'google-voided:'.hash('sha256', 'services.shelf.app|'.$purchase->id.'|'.$void['voidedTimeMillis']);
            $event = PurchaseEvent::where('provider_event_id', $eventId)->first();
            $alreadyRefunded = SalesLedger::withTestPurchases()->where('entry_key', 'refund:'.$purchase->id)->exists();
            $event ??= PurchaseEvent::create([
                'provider_event_id' => $eventId, 'event_type' => 'GOOGLE_VOIDED_PURCHASE',
                'reader_id' => $purchase->reader_id, 'collection_id' => $purchase->collection_id,
                'transaction_id' => $purchase->transaction_id, 'environment' => $purchase->environment,
                'occurred_at' => CarbonImmutable::createFromTimestampMs($void['voidedTimeMillis']),
                'details' => ['source' => 'google_voided_purchases', 'matched_by' => $matchedBy,
                    'voided_source' => $void['voidedSource'] ?? null, 'voided_reason' => $void['voidedReason'] ?? null],
            ]);
            $this->reverse($purchase, 'refund', $event);
            if ($reader && $purchase->book) { $this->refreshAccess($reader, $purchase->book); }
            return ! $alreadyRefunded;
        }, 3);
    }

    private function reverse(Purchase $purchase, string $status, ?PurchaseEvent $event = null): void
    {
        $key = $status.':'.$purchase->id;
        if (SalesLedger::withTestPurchases()->where('entry_key', $key)->exists()) { return; }
        $sale = SalesLedger::withTestPurchases()->where('entry_key', 'sale:'.$purchase->id)->first();
        SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $event?->id, 'entry_key' => $key,
            'status' => $status, 'currency' => $sale?->currency, 'amount' => $status === 'refund' && $sale?->amount !== null ? '-'.$sale->amount : null,
            'occurred_at' => $event?->occurred_at ?? now(), 'agreement_snapshot' => $sale?->agreement_snapshot, 'earnings_status' => $purchase->environment === 'SANDBOX' ? 'test' : 'unknown']);
        $claim = DB::table('purchase_recovery_claims')->where('purchase_id', $purchase->id)->first();
        if ($claim && ($recovered = Reader::find($claim->reader_id))) { $this->refreshAccess($recovered, $purchase->book); }
    }

    public function refreshAccess(Reader $reader, Collection $book): void
    {
        $active = Purchase::where(fn ($q) => $q->where('reader_id', $reader->id)->orWhereIn('id', DB::table('purchase_recovery_claims')->where('reader_id', $reader->id)->select('purchase_id')))->where('collection_id', $book->id)
            ->whereDoesntHave('entries', fn ($q) => $q->whereIn('status', ['refund', 'revoke']))->exists();
        DB::table('book_entitlements')->updateOrInsert(['reader_id' => $reader->id, 'collection_id' => $book->id],
            ['active' => $active, 'checked_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function reconcile(Reader $reader): void
    {
        if (\App\Support\Staging::active() && RevenueCatClient::configured()) {
            $this->confirmStaging($reader);
        }
        $purchases = Purchase::where(fn ($q) => $q->where('reader_id', $reader->id)->orWhereIn('id', DB::table('purchase_recovery_claims')->where('reader_id', $reader->id)->select('purchase_id')))->with(['book', 'entries'])->get();
        if ($purchases->isEmpty()) { return; }
        $needsCheck = $purchases->contains(fn ($purchase) => ! $purchase->entries->contains(fn ($entry) => in_array($entry->status, ['refund', 'revoke'], true)));
        $subscribers = [];
        if ($needsCheck) {
            foreach ($purchases as $purchase) {
                if ($purchase->entries->contains(fn ($entry) => in_array($entry->status, ['refund', 'revoke'], true))) { continue; }
                if (! isset($subscribers[$purchase->reader_id])) {
                    $identity = new Reader;
                    $identity->id = $purchase->reader_id;
                    $subscribers[$purchase->reader_id] = $this->provider->subscriber($identity);
                }
            }
        }
        DB::transaction(function () use ($reader, $subscribers, $purchases): void {
            Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
            foreach ($purchases as $purchase) {
                if (! $purchase->entries()->whereIn('status', ['refund', 'revoke'])->exists()
                    && ! $this->provider->confirms($subscribers[$purchase->reader_id], $purchase->book, $purchase->purchased_at, $purchase->environment)) {
                    $this->reverse($purchase, 'revoke');
                }
                $this->refreshAccess($reader, $purchase->book);
            }
        }, 3);
    }

    private function confirmStaging(Reader $reader): void
    {
        $subscriber = $this->provider->subscriber($reader);
        foreach ($subscriber['non_subscriptions'] as $product => $transactions) {
            $book = Collection::withTrashed()->where('product_id', $product)->first();
            if (! $book || ! is_array($transactions)) { continue; }
            foreach ($transactions as $transaction) {
                if (! is_string($transaction['id'] ?? null) || blank($transaction['id'])
                    || ! is_string($transaction['purchase_date'] ?? null)) { continue; }
                $date = CarbonImmutable::parse($transaction['purchase_date']);
                if (! $this->provider->confirms($subscriber, $book, $date)
                    || ($transaction['is_sandbox'] ?? null) !== true
                    || ($transaction['store'] ?? null) !== 'play_store' || ! empty($transaction['refunded_at'])) { continue; }
                if (! DB::table('purchase_consents')->where('reader_id', $reader->id)->where('collection_id', $book->id)
                    ->where('created_at', '<=', $date)->exists()) { continue; }
                $id = 'staging-rest:'.hash('sha256', $reader->id.'|'.$product.'|'.$transaction['id']);
                $this->receive(['id' => $id, 'type' => 'NON_RENEWING_PURCHASE', 'environment' => 'SANDBOX',
                    'store' => 'PLAY_STORE', 'app_id' => config('purchases.app_id'),
                    'app_user_id' => \App\Support\Staging::identity($reader->id), 'product_id' => $product,
                    'transaction_id' => $id, 'purchased_at_ms' => $date->getTimestampMs(), 'event_timestamp_ms' => now()->getTimestampMs()]);
            }
        }
    }
}
