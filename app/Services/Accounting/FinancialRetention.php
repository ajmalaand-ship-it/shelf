<?php

namespace App\Services\Accounting;

use App\Models\FinancialRetentionRecord as Record;
use App\Models\Purchase;
use App\Models\SalesLedger;
use App\Models\AccountingEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Metadata and read-only eligibility only. Never changes purchase or financial history. */
class FinancialRetention
{
    public function record(User $owner, string $kind, array $data): Record
    {
        abort_unless($owner->is_owner, 403);
        $rules = match ($kind) {
            'tax_return' => ['period' => 'required|string|max:100', 'jurisdiction' => 'required|string|max:100',
                'filed_on' => 'required|date_format:Y-m-d|before_or_equal:today', 'reference' => 'required|string|max:160',
                'amendment_reviewed' => 'accepted'],
            'mapping' => ['purchase_id' => 'required|integer|exists:purchases,id', 'return_ids' => 'required|array|min:1',
                'return_ids.*' => ['required', 'integer', Rule::exists('financial_retention_records', 'id')->where('kind', 'tax_return')],
                'complete' => 'accepted', 'reference' => 'required|string|max:160'],
            'hold' => ['purchase_id' => 'required|integer|exists:purchases,id', 'reason' => ['required', Rule::in(['legal', 'dispute', 'unpaid_balance'])],
                'reference' => 'required|string|max:160', 'release_condition' => 'required|string|max:500',
                'review_on' => 'required|date_format:Y-m-d|after_or_equal:today'],
            'release' => ['hold_id' => ['required', 'integer', Rule::exists('financial_retention_records', 'id')->where('kind', 'hold')],
                'reference' => 'required|string|max:160', 'resolution' => 'required|string|max:500'],
            default => abort(422),
        };
        $data = validator($data, $rules)->validate();
        return DB::transaction(function () use ($owner, $kind, $data) {
            // Serialize metadata writes; never lock or update financial rows.
            User::whereKey($owner->id)->lockForUpdate()->firstOrFail();
            if ($kind === 'release') {
                $hold = Record::findOrFail($data['hold_id']);
                abort_if(Record::where('kind', 'release')->get()->contains(fn ($r) => $r->evidence['hold_id'] == $hold->id), 422, 'Hold already released.');
                $data['purchase_id'] = $hold->purchase_id;
            }
            if ($kind === 'mapping') {
                $data['chain'] = $this->chain((int) $data['purchase_id']);
            }
            return Record::create(['kind' => $kind, 'purchase_id' => $data['purchase_id'] ?? null,
                'created_by' => $owner->id, 'evidence' => $data]);
        });
    }

    private function chain(int $purchaseId): array
    {
        $ledger = SalesLedger::withTestPurchases()->where('purchase_id', $purchaseId)->orderBy('id')->pluck('id')->all();
        return ['ledger' => $ledger, 'accounting' => AccountingEntry::withTestPurchases()->whereIn('sales_ledger_id', $ledger)->orderBy('id')->pluck('id')->all()];
    }

    public function report(): array
    {
        $metadata = Record::orderBy('id')->get();
        $returns = $metadata->where('kind', 'tax_return')->keyBy('id');
        $released = $metadata->where('kind', 'release')->pluck('evidence.hold_id')->all();
        $balances = app(AccountingService::class)->purchaseBalances()->groupBy('purchase_id');
        $rows = [];
        foreach (Purchase::orderBy('id')->cursor() as $purchase) {
            $events = $metadata->where('purchase_id', $purchase->id);
            $mapping = $events->where('kind', 'mapping')->last();
            $reasons = [];
            $dates = [];
            if ($mapping && ($mapping->evidence['chain'] ?? null) !== $this->chain($purchase->id)) {
                $reasons[] = 'Financial correction chain changed; review and remap';
            }
            $returnIds = $mapping?->evidence['return_ids'] ?? [];
            if (! $mapping || ! ($mapping->evidence['complete'] ?? false)) {
                $reasons[] = 'Missing complete tax-return mapping';
            }
            foreach ($returnIds as $id) {
                $return = $returns->get($id);
                if (! $return || ! ($return->evidence['filed_on'] ?? null)) {
                    $reasons[] = 'Missing filing evidence';
                    continue;
                }
                // Calendar years; Feb 29 becomes Feb 28. Full boundary day retained in UTC.
                $dates[] = CarbonImmutable::parse($return->evidence['filed_on'], 'UTC')->addYearsNoOverflow(3)->endOfDay();
            }
            $due = collect($dates)->sort()->last();
            if ($due && CarbonImmutable::now('UTC')->lessThanOrEqualTo($due)) {
                $reasons[] = 'Three-year filing window still open';
            }
            $holds = $events->where('kind', 'hold')->reject(fn ($h) => in_array($h->id, $released));
            foreach ($holds as $hold) {
                $reasons[] = 'Hold #'.$hold->id.': '.$hold->evidence['reason'].(CarbonImmutable::parse($hold->evidence['review_on'], 'UTC')->endOfDay()->isPast() ? ' (review overdue; still held)' : '');
            }
            $ledger = SalesLedger::withTestPurchases()->where('purchase_id', $purchase->id)->get();
            $journal = AccountingEntry::withTestPurchases()->whereIn('sales_ledger_id', $ledger->pluck('id'))->get();
            $real = $purchase->environment === 'PRODUCTION';
            if ($real) {
                $purchaseBalances = $balances->get($purchase->id, collect());
                if ($purchaseBalances->isEmpty() || $purchaseBalances->contains(fn ($b) => $b['unknown'] > 0 || $b['payable'] === null)) {
                    $reasons[] = 'Unknown settlement or author balance';
                } elseif ($purchaseBalances->contains(fn ($b) => Decimal::units($b['payable']) !== 0)) {
                    $reasons[] = 'Unsettled author balance';
                }
            } else {
                // No unapproved test-history expiry policy.
                $reasons[] = 'Test / unverified history: excluded from real income; retained';
            }
            $rows[] = ['purchase_id' => $purchase->id, 'mode' => $real ? 'Real' : 'Test / unverified',
                'ledger_ids' => $ledger->pluck('id')->all(), 'accounting_ids' => $journal->pluck('id')->all(),
                'share_ids' => DB::table('accounting_shares')->whereIn('accounting_entry_id', $journal->pluck('id'))->orderBy('id')->pluck('id')->all(),
                'agreement_ids' => $ledger->pluck('agreement_snapshot.id')->filter()->unique()->values()->all(),
                'provider_event_count' => DB::table('purchase_events')->where('reader_id', $purchase->reader_id)->where('collection_id', $purchase->collection_id)->where('transaction_id', $purchase->transaction_id)->where('store', $purchase->store)->count(),
                'recovery_audit_count' => DB::table('purchase_recoveries')->where('purchase_id', $purchase->id)->count(),
                'return_ids' => $returnIds, 'mapping_id' => $mapping?->id,
                'review_after' => $due?->addSecond()->toDateString(),
                'status' => $reasons ? 'Blocked / retained' : 'Eligible for preservation review only',
                'reasons' => $reasons, 'hold_ids' => $holds->pluck('id')->all(),
                'recovery' => 'Retain original purchase, provider linkage, refund/revocation state and recovery claim/journal; shared agreements and event evidence remain protected.'];
        }
        return ['rows' => $rows, 'metadata' => $metadata, 'eligible' => collect($rows)->where('status', 'Eligible for preservation review only')->count(),
            'blocked' => collect($rows)->where('status', 'Blocked / retained')->count()];
    }
}
