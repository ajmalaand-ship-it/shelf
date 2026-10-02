<?php

namespace App\Console\Commands;

use App\Models\AccountingEntry;
use App\Models\Author;
use App\Models\AuthorShareAgreement;
use App\Models\Collection;
use App\Models\Purchase;
use App\Models\SalesLedger;
use App\Models\User;
use App\Services\Accounting\AccountingService;
use App\Support\Staging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CheckAccounting extends Command
{
    protected $signature = 'shelf:check-accounting {--simulate-after-backup : Staging-only synthetic accounting; all rows rolled back}';

    protected $description = 'Read-only accounting health, or a rolled-back staging simulation.';

    public function handle(AccountingService $service): int
    {
        if (! $this->option('simulate-after-backup')) {
            foreach (['accounting_entries', 'accounting_shares'] as $t) {
                if (! Schema::hasTable($t)) {
                    $this->error('Accounting schema missing.');

                    return self::FAILURE;
                }
            }
            $bad = AccountingEntry::whereHas('ledger.purchase', fn ($q) => $q->where('environment', '!=', 'PRODUCTION'))->count();
            if ($bad) {
                $this->error('Test exclusion check failed.');

                return self::FAILURE;
            }
            $this->info('PASS: accounting schema present; ordinary accounting queries exclude Test; owner-only tools registered; recordkeeping only, no payouts.');

            return self::SUCCESS;
        }
        if (! Staging::active() && ! app()->runningUnitTests()) {
            $this->error('Accounting simulation is forbidden on production.');

            return self::FAILURE;
        }
        $tables = ['users', 'authors', 'collections', 'readers', 'purchases', 'purchase_events', 'sales_ledger', 'book_entitlements', 'author_share_agreements', 'accounting_entries', 'accounting_shares'];
        $snapshot = function () use ($tables) {
            $out = [];
            foreach ($tables as $t) {
                $out[$t] = hash('sha256', json_encode(DB::table($t)->orderBy('id')->get()));
            }

            return $out;
        };
        $before = $snapshot();
        $level = DB::transactionLevel();
        DB::beginTransaction();
        $passed = false;
        try {
            $owner = User::where('is_owner', true)->firstOrFail();
            $tag = bin2hex(random_bytes(8));
            $author = Author::create(['name' => 'Synthetic accounting '.$tag]);
            $book = Collection::create(['title' => 'Synthetic accounting '.$tag]);
            $agreement = AuthorShareAgreement::create(['collection_id' => $book->id, 'contributors' => [['author_id' => $author->id, 'percentage' => 100]], 'basis' => 'net', 'deductions' => 'Confirmed net receipts; no further deductions', 'sharing_terms' => 'Synthetic simulation', 'starts_at' => now()->subDay()]);
            $p = Purchase::create(['collection_id' => $book->id, 'store' => 'PLAY_STORE', 'environment' => 'PRODUCTION', 'transaction_id' => 'GPA.synthetic-accounting-'.$tag, 'product_id' => $book->product_id, 'purchased_at' => now()->subHour()]);
            $l = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'sale:'.$p->id, 'status' => 'sale', 'currency' => 'USD', 'amount' => '10', 'occurred_at' => $p->purchased_at, 'agreement_snapshot' => $agreement->toArray()]);
            $data = ['sales_ledger_id' => $l->id, 'kind' => 'confirmation', 'request_key' => 'synthetic-'.$tag, 'reference' => 'synthetic-'.$tag, 'note' => 'Synthetic rolled-back check', 'currency' => 'USD', 'occurred_at' => now()->toDateTimeString(), 'net_amount' => '8'];
            $c = $service->record($owner, $data);
            if (SalesLedger::forRightsHolder($author->id)->whereKey($l->id)->count() !== 1) {
                throw new \RuntimeException;
            }
            if ($service->record($owner, $data)->id !== $c->id) {
                throw new \RuntimeException;
            }
            $pay = $service->record($owner, array_replace($data, ['kind' => 'payment', 'request_key' => 'payment-'.$tag, 'reference' => 'bank-'.$tag, 'author_id' => $author->id, 'amount' => '2', 'net_amount' => null]));
            $r = $service->purchaseBalances($p->id)->first();
            if ($r['confirmed_owed'] !== '8.000000' || $r['paid'] !== '2.000000' || $r['payable'] !== '6.000000') {
                throw new \RuntimeException;
            }
            $service->reverse($owner, $pay, 'bank-reversal-'.$tag, 'Synthetic reversal', 'reverse-'.$tag);
            $refund = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'refund:'.$p->id, 'status' => 'refund', 'currency' => 'USD', 'amount' => '-10', 'occurred_at' => now(), 'agreement_snapshot' => $agreement->toArray()]);
            if ($service->purchaseBalances($p->id)->first()['payable'] !== null) {
                throw new \RuntimeException;
            }
            $service->record($owner, array_replace($data, ['sales_ledger_id' => $refund->id, 'request_key' => 'refund-'.$tag, 'reference' => 'refund-report-'.$tag, 'net_amount' => '-8']));
            if ($service->purchaseBalances($p->id)->first()['confirmed_owed'] !== '0.000000') {
                throw new \RuntimeException;
            }
            $test = Purchase::create(['collection_id' => $book->id, 'store' => 'PLAY_STORE', 'environment' => 'SANDBOX', 'transaction_id' => 'GPA.synthetic-test-'.$tag, 'product_id' => $book->product_id, 'purchased_at' => now()]);
            $t = SalesLedger::create(['purchase_id' => $test->id, 'entry_key' => 'sale:'.$test->id, 'status' => 'sale', 'currency' => 'USD', 'amount' => '999', 'occurred_at' => now(), 'agreement_snapshot' => $agreement->toArray()]);
            try {
                $service->record($owner, array_replace($data, ['sales_ledger_id' => $t->id, 'request_key' => 'test-'.$tag, 'reference' => 'test-'.$tag]));
                throw new \RuntimeException;
            } catch (ValidationException) {
            }
            if ($service->purchaseBalances($test->id)->isNotEmpty()) {
                throw new \RuntimeException;
            }
            $passed = true;
        } catch (\Throwable) {
            $this->error('Accounting simulation failed; private data withheld.');
        } finally {
            while (DB::transactionLevel() > $level) {
                DB::rollBack();
            }
        }
        if ($snapshot() !== $before) {
            $this->error('Accounting rollback fingerprint mismatch; stop.');

            return self::FAILURE;
        }
        if (! $passed) {
            return self::FAILURE;
        }
        $this->info('PASS: staged D10 net calculation, duplicate protection, payment/reversal, unknown-refund hold and Test exclusion; synthetic history rolled back, prior fingerprints unchanged.');

        return self::SUCCESS;
    }
}
