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
        abort_unless(($event['environment'] ?? '') === 'SANDBOX' && ($event['store'] ?? '') === 'PLAY_STORE'
            && ($event['app_id'] ?? '') === config('purchases.app_id'), 422, 'Only Shelf Google Play sandbox events are accepted.');
        $type = $event['type'];
        if ($type === 'TEST') { return; }
        abort_unless(in_array($type, ['NON_RENEWING_PURCHASE', 'CANCELLATION', 'EXPIRATION'], true), 422, 'Unsupported event; no access granted.');
        $book = Collection::withTrashed()->where('product_id', $event['product_id'])->firstOrFail();
        abort_unless($book->product_id === 'shelf_book_'.$book->id, 422);
        $reader = Reader::find($event['app_user_id']);
        if (! $reader) {
            // Deleting a login never deletes the financial trail or prevents later refunds.
            $historical = Purchase::where('store', 'PLAY_STORE')->where('environment', 'SANDBOX')
                ->where('transaction_id', $event['transaction_id'])->where('collection_id', $book->id)
                ->where('reader_id', (int) $event['app_user_id'])->first();
            abort_unless($type !== 'NON_RENEWING_PURCHASE' && $historical, 404);
        }
        if (PurchaseEvent::where('provider_event_id', $event['id'])->exists()) { return; }
        $purchasedAt = CarbonImmutable::createFromTimestampMs($event['purchased_at_ms']);
        $occurredAt = CarbonImmutable::createFromTimestampMs($event['event_timestamp_ms']);
        $subscriber = $type === 'NON_RENEWING_PURCHASE' ? $this->provider->subscriber($reader) : null;
        DB::transaction(function () use ($event, $type, $book, $reader, $purchasedAt, $occurredAt, $subscriber): void {
            if ($reader) { Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail(); }
            if (PurchaseEvent::where('provider_event_id', $event['id'])->exists()) { return; }
            $purchase = Purchase::where('store', 'PLAY_STORE')->where('environment', 'SANDBOX')->where('transaction_id', $event['transaction_id'])->lockForUpdate()->first();
            abort_if($purchase && ($purchase->reader_id !== (int) $event['app_user_id'] || $purchase->collection_id !== $book->id), 409, 'Purchase belongs to another account or book.');
            if ($type === 'NON_RENEWING_PURCHASE') {
                // Confirmation may arrive after withdrawal; previously initiated purchases remain valid.
                abort_unless($this->provider->confirms($subscriber, $book, $purchasedAt), 503, 'Confirming your purchase. Please try again.');
                if (! $purchase) {
                    abort_unless(DB::table('purchase_consents')->where('reader_id', $reader->id)->where('collection_id', $book->id)
                        ->where('created_at', '<=', $purchasedAt->addSeconds(5))->exists(), 409, 'Purchase agreement is missing. Contact Shelf support.');
                }
            }
            $entry = PurchaseEvent::create([
                'provider_event_id' => $event['id'], 'event_type' => $type, 'reader_id' => (int) $event['app_user_id'],
                'collection_id' => $book->id, 'transaction_id' => $event['transaction_id'], 'environment' => 'SANDBOX',
                'occurred_at' => $occurredAt, 'details' => ['currency' => $event['currency'] ?? null,
                    'amount' => $event['price_in_purchased_currency'] ?? null, 'cancel_reason' => $event['cancel_reason'] ?? null],
            ]);
            if ($type === 'NON_RENEWING_PURCHASE') {
                $purchase ??= Purchase::create(['reader_id' => $reader->id, 'collection_id' => $book->id, 'store' => 'PLAY_STORE',
                    'environment' => 'SANDBOX', 'transaction_id' => $event['transaction_id'], 'product_id' => $book->product_id, 'purchased_at' => $purchasedAt]);
                if (! SalesLedger::where('entry_key', 'sale:'.$purchase->id)->exists()) {
                    $agreement = AuthorShareAgreement::where('collection_id', $book->id)->where('starts_at', '<=', $purchasedAt)->orderByDesc('starts_at')->orderByDesc('id')->first();
                    $amount = $event['price_in_purchased_currency'] ?? null;
                    $estimates = null;
                    if ($agreement?->basis === 'gross' && strtolower(trim($agreement->deductions)) === 'none' && is_numeric($amount)) {
                        $estimates = array_map(fn ($c) => ['author_id' => $c['author_id'], 'amount' => (string) \Brick\Math\BigDecimal::of((string) $amount)
                            ->multipliedBy((string) $c['percentage'])->dividedBy('100', 6, \Brick\Math\RoundingMode::HALF_UP)], $agreement->contributors);
                    }
                    SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $entry->id, 'entry_key' => 'sale:'.$purchase->id,
                        'status' => 'sale', 'currency' => $event['currency'] ?? null, 'amount' => $amount,
                        'occurred_at' => $purchasedAt, 'agreement_snapshot' => $agreement?->toArray(),
                        'estimated_earnings' => $estimates, 'earnings_status' => $estimates ? 'provisional' : 'unknown']);
                }
                // A refund received before the sale is a tombstone, not permission to unlock.
                foreach (PurchaseEvent::where('transaction_id', $purchase->transaction_id)->where('collection_id', $book->id)
                    ->whereIn('event_type', ['CANCELLATION', 'EXPIRATION'])->get() as $reversal) {
                    $this->reverse($purchase, $reversal->event_type === 'CANCELLATION' ? 'refund' : 'revoke', $reversal);
                }
            } elseif ($purchase) {
                $this->reverse($purchase, $type === 'CANCELLATION' ? 'refund' : 'revoke', $entry);
            }
            if ($reader) { $this->refreshAccess($reader, $book); }
        }, 3);
    }

    private function reverse(Purchase $purchase, string $status, ?PurchaseEvent $event = null): void
    {
        $key = $status.':'.$purchase->id;
        if (SalesLedger::where('entry_key', $key)->exists()) { return; }
        $sale = SalesLedger::where('entry_key', 'sale:'.$purchase->id)->first();
        SalesLedger::create(['purchase_id' => $purchase->id, 'event_id' => $event?->id, 'entry_key' => $key,
            'status' => $status, 'currency' => $sale?->currency, 'amount' => $status === 'refund' && $sale?->amount !== null ? '-'.$sale->amount : null,
            'occurred_at' => $event?->occurred_at ?? now(), 'agreement_snapshot' => $sale?->agreement_snapshot]);
    }

    private function refreshAccess(Reader $reader, Collection $book): void
    {
        $active = Purchase::where('reader_id', $reader->id)->where('collection_id', $book->id)
            ->whereDoesntHave('entries', fn ($q) => $q->whereIn('status', ['refund', 'revoke']))->exists();
        DB::table('book_entitlements')->updateOrInsert(['reader_id' => $reader->id, 'collection_id' => $book->id],
            ['active' => $active, 'checked_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function reconcile(Reader $reader): void
    {
        if (\App\Support\Staging::active() && RevenueCatClient::configured()) {
            $this->confirmStaging($reader);
        }
        $purchases = Purchase::where('reader_id', $reader->id)->with('book')->get();
        if ($purchases->isEmpty()) { return; }
        $subscriber = $this->provider->subscriber($reader);
        DB::transaction(function () use ($reader, $subscriber, $purchases): void {
            Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
            foreach ($purchases as $purchase) {
                if (! $this->provider->confirms($subscriber, $purchase->book, $purchase->purchased_at)) {
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
