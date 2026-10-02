<?php

namespace App\Services\Accounting;

use App\Models\AccountingEntry;
use App\Models\AccountingShare;
use App\Models\Author;
use App\Models\SalesLedger;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingService
{
    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['accounting' => $message]);
    }

    private function owner(User $owner): void
    {
        abort_unless($owner->is_owner, 403);
    }

    private function agreement(SalesLedger $ledger): array
    {
        $s = $ledger->agreement_snapshot;
        if (! is_array($s) || empty($s['contributors']) || ! in_array($s['basis'] ?? null, ['gross', 'net'], true)
            || empty($s['deductions']) || empty($s['starts_at'])
            || CarbonImmutable::parse($s['starts_at'])->greaterThan($ledger->purchase->purchased_at)) {
            $this->invalid('The sale has no valid historical agreement. Do not apply a later agreement to this sale.');
        }
        $seen = [];
        $total = 0;
        foreach ($s['contributors'] as $c) {
            if (! isset($c['author_id'],$c['percentage']) || isset($seen[$c['author_id']]) || ! Author::whereKey($c['author_id'])->exists()) {
                $this->invalid('Historical rights holder is invalid.');
            }
            $p = Decimal::units((string) $c['percentage']);
            if ($p <= 0 || $p > 100000000) {
                $this->invalid('Historical percentage is invalid.');
            }
            $total += $p;
            $seen[$c['author_id']] = true;
        }
        if ($total > 100000000) {
            $this->invalid('Historical percentages exceed 100%.');
        }

        return $s;
    }

    public function record(User $owner, array $data): AccountingEntry
    {
        $this->owner($owner);
        $v = validator($data, [
            'sales_ledger_id' => 'required|integer', 'kind' => 'required|in:estimate,confirmation,adjustment,payment',
            'request_key' => 'required|string|max:64', 'reference' => 'required|string|max:160', 'note' => 'required|string|max:4000',
            'currency' => 'required|regex:/^[A-Z]{3}$/D', 'occurred_at' => 'required|date|before_or_equal:now',
            'net_amount' => 'nullable', 'fees_amount' => 'nullable', 'taxes_amount' => 'nullable', 'amount' => 'nullable',
            'author_id' => 'nullable|integer',
        ])->validate();
        foreach (['net_amount', 'fees_amount', 'taxes_amount', 'amount'] as $f) {
            $v[$f] = isset($v[$f]) && $v[$f] !== '' ? Decimal::text(Decimal::units($v[$f])) : null;
        }
        $v['sales_ledger_id'] = (int) $v['sales_ledger_id'];
        $v['author_id'] = isset($v['author_id']) ? (int) $v['author_id'] : null;
        $v['reference'] = trim($v['reference']);
        $v['note'] = trim($v['note']);
        if ($v['reference'] === '' || $v['note'] === '') {
            $this->invalid('A supporting reference and short explanation are required.');
        }
        $v['occurred_at'] = CarbonImmutable::parse($v['occurred_at'])->utc()->format('Y-m-d H:i:s');
        ksort($v);
        $hash = hash('sha256', json_encode($v));

        return DB::transaction(function () use ($owner, $v, $hash) {
            $ledger = SalesLedger::withTestPurchases()->lockForUpdate()->findOrFail($v['sales_ledger_id']);
            // Serialize against refund detection without modifying purchase or access.
            $ledger->setRelation('purchase', $ledger->purchase()->lockForUpdate()->firstOrFail());
            $source = hash('sha256', $ledger->id.'|'.$v['kind'].'|'.$v['reference'].'|'.($v['kind'] === 'payment' ? ($v['author_id'] ?? '') : ''));
            $existing = AccountingEntry::withTestPurchases()->where(fn ($q) => $q->where('request_key', $v['request_key'])->orWhere('source_key', $source))->first();
            if ($existing) {
                $compare = $v;
                $compare['request_key'] = $existing->request_key;
                ksort($compare);
                if ($existing->payload_hash !== hash('sha256', json_encode($compare))) {
                    $this->invalid('That request or reference already belongs to different figures.');
                }

                return $existing;
            }
            if ($ledger->is_test) {
                $this->invalid('Test purchases have no real income, owed amount or payment. Keep them in Sales ledger history only.');
            }
            if (! in_array($ledger->status, ['sale', 'refund', 'adjustment'], true)) {
                $this->invalid('This is an access event, not a financial sale or refund.');
            }
            if ($ledger->currency && $ledger->currency !== $v['currency']) {
                $this->invalid('Currency must match the verified sale; no exchange conversion is assumed.');
            }
            $snapshot = $this->agreement($ledger);
            $kind = $v['kind'];
            if ($kind === 'confirmation' && AccountingEntry::where('sales_ledger_id', $ledger->id)->where('kind', 'confirmation')->whereDoesntHave('reversal')->exists()) {
                $this->invalid('Confirmed figures already exist. Reverse that entry first, then record corrected figures with a new reference.');
            }
            if ($kind === 'estimate' && AccountingEntry::where('sales_ledger_id', $ledger->id)->where('kind', 'estimate')->whereDoesntHave('reversal')->exists()) {
                $this->invalid('An estimate already exists. Reverse it before adding a corrected estimate.');
            }
            if (in_array($kind, ['estimate', 'confirmation'], true)) {
                if ($snapshot['basis'] === 'net' && $v['net_amount'] === null) {
                    $this->invalid('Net receipts are unknown. Enter an evidenced estimate or confirmed net receipts; do not assume fees or taxes are zero.');
                }
                if ($snapshot['basis'] === 'gross' && $ledger->amount === null) {
                    $this->invalid('Verified gross amount is unknown.');
                }
                foreach (['net_amount'] as $f) {
                    if ($v[$f] !== null && (($ledger->status === 'sale' && Decimal::units($v[$f]) < 0) || ($ledger->status === 'refund' && Decimal::units($v[$f]) > 0))) {
                        $this->invalid('Use positive figures for a sale and negative figures for a refund.');
                    }
                }
            }
            if ($kind === 'adjustment') {
                if ($v['amount'] === null) {
                    $this->invalid('Enter the signed adjustment to the agreement calculation basis.');
                }
                if (! AccountingEntry::where('sales_ledger_id', $ledger->id)->where('kind', 'confirmation')->whereDoesntHave('reversal')->exists()) {
                    $this->invalid('Confirm source figures before recording an adjustment.');
                }
            }
            if ($kind === 'payment') {
                if ($ledger->status !== 'sale' || $v['amount'] === null || Decimal::units($v['amount']) <= 0) {
                    $this->invalid('Record a positive payment against the original sale.');
                }

            }
            $base = $kind === 'payment' ? null : ($kind === 'adjustment' ? $v['amount'] : ($snapshot['basis'] === 'net' ? $v['net_amount'] : $ledger->amount));
            $entry = AccountingEntry::create([
                'sales_ledger_id' => $ledger->id, 'request_key' => $v['request_key'], 'source_key' => $source, 'payload_hash' => $hash,
                'kind' => $kind, 'currency' => $v['currency'], 'reference' => $v['reference'], 'note' => $v['note'],
                'occurred_at' => $v['occurred_at'], 'created_by' => $owner->id, 'agreement_snapshot' => $snapshot,
                'basis_amount' => $base,
                'gross_amount' => in_array($kind, ['estimate', 'confirmation'], true) ? $ledger->amount : null,
                'fees_amount' => in_array($kind, ['estimate', 'confirmation'], true) ? $v['fees_amount'] : null, 'taxes_amount' => in_array($kind, ['estimate', 'confirmation'], true) ? $v['taxes_amount'] : null,
                'net_amount' => $kind === 'adjustment' ? ($snapshot['basis'] === 'net' ? $v['amount'] : null) : (in_array($kind, ['estimate', 'confirmation'], true) ? $v['net_amount'] : null),
                'adjusts_id' => $kind === 'adjustment' ? AccountingEntry::where('sales_ledger_id', $ledger->id)->where('kind', 'confirmation')->whereDoesntHave('reversal')->sole()->id : null,
            ]);
            if ($kind === 'payment') {
                $c = collect($snapshot['contributors'])->firstWhere('author_id', $v['author_id']);
                if (! $c) {
                    $this->invalid('This rights holder is absent from the historical agreement.');
                }
                AccountingShare::create(['accounting_entry_id' => $entry->id, 'author_id' => $c['author_id'], 'percentage' => $c['percentage'], 'paid' => $v['amount']]);
            } else {
                $base = $kind === 'adjustment' ? $v['amount'] : ($snapshot['basis'] === 'net' ? $v['net_amount'] : $ledger->amount);
                foreach ($snapshot['contributors'] as $c) {
                    AccountingShare::create([
                        'accounting_entry_id' => $entry->id, 'author_id' => $c['author_id'], 'percentage' => $c['percentage'],
                        $kind === 'estimate' ? 'estimated' : 'owed' => Decimal::text(Decimal::share(Decimal::units($base), $c['percentage'])),
                    ]);
                }
            }

            return $entry;
        }, 3);
    }

    public function reverse(User $owner, AccountingEntry $entry, string $reference, string $note, string $requestKey, ?string $occurredAt = null): AccountingEntry
    {
        $this->owner($owner);
        if (trim($reference) === '' || trim($note) === '') {
            $this->invalid('Reference and explanation are required.');
        }
        validator(compact('reference', 'note', 'requestKey'), ['reference' => 'required|string|max:160', 'note' => 'required|string|max:4000', 'requestKey' => 'required|string|max:64'])->validate();

        if ($occurredAt !== null) {
            validator(['occurred_at' => $occurredAt], ['occurred_at' => 'required|date|before_or_equal:now'])->validate();
        }
        $date = $occurredAt === null ? null : CarbonImmutable::parse($occurredAt)->utc()->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($owner, $entry, $reference, $note, $requestKey, $date) {
            $entry = AccountingEntry::withTestPurchases()->lockForUpdate()->findOrFail($entry->id);
            $ledger = SalesLedger::withTestPurchases()->lockForUpdate()->findOrFail($entry->sales_ledger_id);
            $ledger->setRelation('purchase', $ledger->purchase()->lockForUpdate()->firstOrFail());
            $entry->setRelation('ledger', $ledger);
            if ($entry->ledger->is_test) {
                $this->invalid('Test history cannot create real financial reversals.');
            }
            if (in_array($entry->kind, ['reversal', 'payment_reversal'], true)) {
                $this->invalid('Reverse the original entry once; never reverse a reversal.');
            }
            $payload = hash('sha256', json_encode([$entry->id, trim($reference), trim($note), $requestKey, $date]));
            $old = AccountingEntry::withTestPurchases()->where(fn ($q) => $q->where('reverses_id', $entry->id)->orWhere('request_key', $requestKey))->first();
            if ($old) {
                if ($old->payload_hash !== $payload) {
                    $this->invalid('This reversal already has a different supporting reference.');
                }

                return $old;
            }
            if ($entry->kind === 'confirmation' && AccountingEntry::where('sales_ledger_id', $entry->sales_ledger_id)->where('kind', 'adjustment')->whereDoesntHave('reversal')->exists()) {
                $this->invalid('Reverse linked adjustments before correcting confirmed receipts.');
            }
            $fields = ['sales_ledger_id' => $entry->sales_ledger_id, 'reverses_id' => $entry->id, 'request_key' => $requestKey,
                'source_key' => hash('sha256', 'reverse|'.$entry->id), 'payload_hash' => $payload, 'kind' => $entry->kind === 'payment' ? 'payment_reversal' : 'reversal',
                'currency' => $entry->currency, 'reference' => trim($reference), 'note' => trim($note), 'occurred_at' => $date ?? now(), 'created_by' => $owner->id, 'agreement_snapshot' => $entry->agreement_snapshot];
            foreach (['gross_amount', 'fees_amount', 'taxes_amount', 'net_amount', 'basis_amount'] as $f) {
                $fields[$f] = $entry->$f === null ? null : Decimal::text(-Decimal::units($entry->$f));
            }
            $reversal = AccountingEntry::create($fields);
            foreach ($entry->shares as $share) {
                $data = ['accounting_entry_id' => $reversal->id, 'author_id' => $share->author_id, 'percentage' => $share->percentage];
                foreach (['estimated', 'owed', 'paid'] as $f) {
                    $data[$f] = $share->$f === null ? null : Decimal::text(-Decimal::units($share->$f));
                }
                AccountingShare::create($data);
            }

            return $reversal;
        }, 3);
    }

    /** One row per purchase / rights holder / currency. Unknown liabilities suspend payments. */
    public function purchaseBalances(?int $purchaseId = null, bool $lock = false): Collection
    {
        $ledgers = SalesLedger::with('purchase.book')->when($purchaseId, fn ($q) => $q->where('purchase_id', $purchaseId))->whereIn('status', ['sale', 'refund', 'adjustment'])->orderBy('id')->when($lock, fn ($q) => $q->lockForUpdate())->get();
        $entries = AccountingEntry::with(['shares' => fn ($q) => $q->when($lock, fn ($q) => $q->lockForUpdate()), 'reversal' => fn ($q) => $q->when($lock, fn ($q) => $q->lockForUpdate())])->whereIn('sales_ledger_id', $ledgers->pluck('id'))->when($lock, fn ($q) => $q->lockForUpdate())->get()->groupBy('sales_ledger_id');
        $out = [];
        foreach ($ledgers as $ledger) {
            $snapshot = $ledger->agreement_snapshot;
            $currency = $ledger->currency;
            $journal = $entries->get($ledger->id, collect());
            if (! $currency) {
                $currency = $journal->first()?->currency ?? 'Unknown';
            }
            $confirmed = $journal->contains(fn ($e) => $e->kind === 'confirmation' && ! $e->reversal);
            $estimate = $journal->first(fn ($e) => $e->kind === 'estimate' && ! $e->reversal);
            foreach ($snapshot['contributors'] ?? [] as $c) {
                $key = $ledger->purchase_id.'|'.$c['author_id'].'|'.$currency;
                $out[$key] ??= ['purchase_id' => $ledger->purchase_id, 'book_id' => $ledger->purchase->collection_id, 'book' => $ledger->purchase->book?->title ?? 'Book',
                    'author_id' => $c['author_id'], 'rights_holder' => Author::find($c['author_id'])?->name ?? 'Rights holder',
                    'currency' => $currency, 'estimated_units' => 0, 'estimated_unknown' => 0, 'owed_units' => 0, 'paid_units' => 0, 'unknown' => 0];
                if (! $confirmed) {
                    $out[$key]['unknown']++;
                }
                if ($estimate) {
                    $s = $estimate->shares->firstWhere('author_id', $c['author_id']);
                    if ($s?->estimated !== null) {
                        $out[$key]['estimated_units'] += Decimal::units($s->estimated);
                    } else {
                        $out[$key]['estimated_unknown']++;
                    }
                } elseif (($snapshot['basis'] ?? null) === 'gross' && $ledger->amount !== null) {
                    $out[$key]['estimated_units'] += Decimal::share(Decimal::units($ledger->amount), $c['percentage']);
                } else {
                    $out[$key]['estimated_unknown']++;
                }
                foreach ($journal as $e) {
                    foreach ($e->shares as $s) {
                        if ($s->author_id === $c['author_id']) {
                            if ($s->owed !== null) {
                                $out[$key]['owed_units'] += Decimal::units($s->owed);
                            }
                            if ($s->paid !== null) {
                                $out[$key]['paid_units'] += Decimal::units($s->paid);
                            }
                        }
                    }
                }
            }
        }

        return collect($out)->map(function ($r) {
            $r['estimated'] = $r['estimated_unknown'] ? null : Decimal::text($r['estimated_units']);
            $r['confirmed_owed'] = $r['unknown'] ? null : Decimal::text($r['owed_units']);
            $r['known_owed'] = Decimal::text($r['owed_units']);
            $r['paid'] = Decimal::text($r['paid_units']);
            $r['payable'] = $r['unknown'] ? null : Decimal::text($r['owed_units'] - $r['paid_units']);

            return $r;
        })->values();
    }

    public function overview(): array
    {
        $rows = $this->purchaseBalances();
        $groups = [];
        foreach ($rows as $r) {
            $key = $r['book_id'].'|'.$r['author_id'].'|'.$r['currency'];
            $groups[$key] ??= array_merge($r, ['estimated_units' => 0, 'owed_units' => 0, 'paid_units' => 0, 'unknown' => 0, 'estimated_unknown' => 0]);
            foreach (['estimated_units', 'owed_units', 'paid_units', 'unknown', 'estimated_unknown'] as $f) {
                $groups[$key][$f] += $r[$f];
            }
        }

        return array_values(array_map(function ($r) {
            $r['estimated'] = $r['estimated_unknown'] ? null : Decimal::text($r['estimated_units']);
            $r['confirmed_owed'] = $r['unknown'] ? null : Decimal::text($r['owed_units']);
            $r['known_owed'] = Decimal::text($r['owed_units']);
            $r['paid'] = Decimal::text($r['paid_units']);
            $r['payable'] = $r['unknown'] ? null : Decimal::text($r['owed_units'] - $r['paid_units']);

            return $r;
        }, $groups));
    }
}
