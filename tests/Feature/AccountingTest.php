<?php

namespace Tests\Feature;

use App\Filament\Pages\AuthorBalances;
use App\Filament\Resources\Accounting\AccountingEntryResource;
use App\Filament\Resources\Accounting\Pages\CreateAccountingEntry;
use App\Filament\Resources\Accounting\Pages\ListAccountingEntries;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\AccountingEntry;
use App\Models\AccountingShare;
use App\Models\Author;
use App\Models\AuthorShareAgreement;
use App\Models\Collection;
use App\Models\Purchase;
use App\Models\SalesLedger;
use App\Models\User;
use App\Services\Accounting\AccountingService;
use App\Services\Accounting\Decimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Author $author;

    private Collection $book;

    private SalesLedger $sale;

    private AccountingService $service;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['is_owner' => true]);
        $this->actingAs($this->owner);
        $this->author = Author::create(['name' => 'Synthetic rights holder']);
        $this->book = Collection::create(['title' => 'Accounting synthetic']);
        $agreement = AuthorShareAgreement::create(['collection_id' => $this->book->id, 'contributors' => [['author_id' => $this->author->id, 'percentage' => 100]], 'basis' => 'net', 'deductions' => 'Store fees and taxes withheld; no further deductions.', 'sharing_terms' => '100% net', 'starts_at' => now()->subDay()]);
        $p = Purchase::create(['collection_id' => $this->book->id, 'store' => 'PLAY_STORE', 'environment' => 'PRODUCTION', 'transaction_id' => 'GPA.accounting-synthetic', 'product_id' => $this->book->product_id, 'purchased_at' => now()->subHour()]);
        $this->sale = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'sale:'.$p->id, 'status' => 'sale', 'currency' => 'USD', 'amount' => '10.00', 'occurred_at' => $p->purchased_at, 'agreement_snapshot' => $agreement->toArray()]);
        $this->service = app(AccountingService::class);
    }

    private function data(string $kind = 'confirmation', array $extra = []): array
    {
        return array_replace(['sales_ledger_id' => $this->sale->id, 'kind' => $kind, 'request_key' => 'synthetic-request-'.(++$this->seq), 'reference' => 'synthetic-reference-'.$this->seq, 'note' => 'Synthetic supporting settlement evidence', 'currency' => 'USD', 'occurred_at' => now()->toDateTimeString(), 'net_amount' => '8.00'], $extra);
    }

    private function recordEntry(string $kind = 'confirmation', array $extra = []): AccountingEntry
    {
        return $this->service->record($this->owner, $this->data($kind, $extra));
    }

    private function invalid(callable $f): void
    {
        try {
            $f();
            $this->fail('Invalid accounting accepted');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_net_unknown_stays_unknown_until_evidenced_estimate_and_separate_confirmation(): void
    {
        $this->assertNull($this->service->overview()[0]['estimated']);
        $this->assertNull($this->service->overview()[0]['confirmed_owed']);
        $this->invalid(fn () => $this->recordEntry('confirmation', ['net_amount' => null]));
        $e = $this->recordEntry('estimate', ['net_amount' => '7.50']);
        $this->assertNull($e->fees_amount);
        $this->assertNull($e->taxes_amount);
        $r = $this->service->overview()[0];
        $this->assertSame('7.500000', $r['estimated']);
        $this->assertNull($r['payable']);
        $c = $this->recordEntry();
        $r = $this->service->overview()[0];
        $this->assertSame('8.000000', $r['confirmed_owed']);
        $this->assertSame('0.000000', $r['paid']);
        $this->assertSame('8.000000', $r['payable']);
        $this->assertNull($c->fees_amount);
        $this->assertSame('net', $c->agreement_snapshot['basis']);
        $this->assertSame('10.000000', $c->gross_amount);
    }

    public function test_confirmed_zero_is_distinct_from_unknown_and_negative_sale_is_rejected(): void
    {
        $this->invalid(fn () => $this->recordEntry('confirmation', ['net_amount' => '-1']));
        $this->recordEntry('confirmation', ['net_amount' => '0', 'fees_amount' => '0', 'taxes_amount' => '0']);
        $this->assertSame('0.000000', $this->service->overview()[0]['confirmed_owed']);
    }

    public function test_test_and_unknown_modes_cannot_create_real_income_payments_or_balances(): void
    {
        foreach (['SANDBOX', 'UNKNOWN'] as $mode) {
            $p = Purchase::create(['collection_id' => $this->book->id, 'store' => 'PLAY_STORE', 'environment' => $mode, 'transaction_id' => 'synthetic-'.$mode, 'product_id' => $this->book->product_id, 'purchased_at' => now()]);
            $l = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'sale:'.$p->id, 'status' => 'sale', 'currency' => 'USD', 'amount' => '999', 'occurred_at' => now(), 'agreement_snapshot' => $this->sale->agreement_snapshot]);
            $this->invalid(fn () => $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id]));
            $this->invalid(fn () => $this->recordEntry('payment', ['sales_ledger_id' => $l->id, 'author_id' => $this->author->id, 'amount' => '1']));
            $this->assertSame('Test', $l->income_mode);
        }
        $this->assertCount(1, $this->service->overview());
        $this->assertDatabaseCount('accounting_entries', 0);
        $this->get(SaleResource::getUrl())->assertOk()->assertSee('Test');
    }

    public function test_idempotent_request_and_reference_and_conflicting_replay_are_held(): void
    {
        $d = $this->data();
        $e = $this->service->record($this->owner, $d);
        $this->assertSame($e->id, $this->service->record($this->owner, $d)->id);
        $d['request_key'] = 'different-request';
        $this->assertSame($e->id, $this->service->record($this->owner, $d)->id);
        $d['net_amount'] = '9';
        $this->invalid(fn () => $this->service->record($this->owner, $d));
        $this->invalid(fn () => $this->recordEntry());
        $this->assertDatabaseCount('accounting_entries', 1);
    }

    public function test_later_agreements_do_not_recalculate_historical_shares(): void
    {
        $before = $this->sale->fresh()->toArray();
        $old = $this->sale->agreement_snapshot;
        AuthorShareAgreement::create(['collection_id' => $this->book->id, 'contributors' => [['author_id' => $this->author->id, 'percentage' => 25]], 'basis' => 'gross', 'deductions' => 'none', 'sharing_terms' => 'Later terms', 'starts_at' => now()]);
        $e = $this->recordEntry();
        $this->assertSame($old, $e->agreement_snapshot);
        $this->assertSame('8.000000', $e->shares->first()->owed);
        $this->assertEquals($before, $this->sale->fresh()->toArray());
    }

    public function test_missing_or_future_agreement_is_held_without_guessing(): void
    {
        foreach ([null, array_replace($this->sale->agreement_snapshot, ['starts_at' => now()->addDay()->toDateTimeString()])] as $s) {
            $l = SalesLedger::create(['purchase_id' => $this->sale->purchase_id, 'entry_key' => 'synthetic:'.(++$this->seq), 'status' => 'adjustment', 'currency' => 'USD', 'amount' => '1', 'occurred_at' => now(), 'agreement_snapshot' => $s]);
            $this->invalid(fn () => $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id]));
        }
        $this->assertDatabaseCount('accounting_entries', 0);
    }

    public function test_refund_is_linked_and_unknown_net_suspends_payable_balance_until_confirmed(): void
    {
        $this->recordEntry();
        $l = SalesLedger::create(['purchase_id' => $this->sale->purchase_id, 'entry_key' => 'refund:'.$this->sale->purchase_id, 'status' => 'refund', 'currency' => 'USD', 'amount' => '-10', 'occurred_at' => now(), 'agreement_snapshot' => $this->sale->agreement_snapshot]);
        $this->assertNull($this->service->overview()[0]['payable']);
        $this->recordEntry('payment', ['net_amount' => null, 'author_id' => $this->author->id, 'amount' => '1']);
        $this->assertNull($this->service->overview()[0]['payable']);
        $this->assertSame('1.000000', $this->service->overview()[0]['paid']);
        $this->invalid(fn () => $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id, 'net_amount' => '8']));
        $e = $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id, 'net_amount' => '-8']);
        $this->assertSame($l->id, $e->sales_ledger_id);
        $this->assertSame('0.000000', $this->service->overview()[0]['confirmed_owed']);
    }

    public function test_refund_can_preserve_evidenced_unreturned_fees_without_assuming_zero_net(): void
    {
        $this->recordEntry();
        $l = SalesLedger::create(['purchase_id' => $this->sale->purchase_id, 'entry_key' => 'refund:'.$this->sale->purchase_id, 'status' => 'refund', 'currency' => 'USD', 'amount' => '-10', 'occurred_at' => now(), 'agreement_snapshot' => $this->sale->agreement_snapshot]);
        $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id, 'net_amount' => '-7']);
        $this->assertSame('1.000000', $this->service->overview()[0]['confirmed_owed']);
    }

    public function test_adjustments_and_corrections_are_append_only_linked_and_payment_reversal_is_idempotent(): void
    {
        $c = $this->recordEntry();
        $a = $this->recordEntry('adjustment', ['amount' => '-1']);
        $this->assertSame('7.000000', $this->service->overview()[0]['confirmed_owed']);
        $p = $this->recordEntry('payment', ['net_amount' => null, 'author_id' => $this->author->id, 'amount' => '2']);
        $this->assertSame('5.000000', $this->service->overview()[0]['payable']);
        $r = $this->service->reverse($this->owner, $p, 'payment-return-reference', 'Bank returned payment', 'reverse-payment');
        $this->assertSame('payment_reversal', $r->kind);
        $this->assertSame($p->id, $r->reverses_id);
        $this->assertSame($r->id, $this->service->reverse($this->owner, $p, 'payment-return-reference', 'Bank returned payment', 'reverse-payment')->id);
        $this->assertSame('0.000000', $this->service->overview()[0]['paid']);
        $this->invalid(fn () => $this->service->reverse($this->owner, $c, 'reverse-confirmation', 'Correct receipts', 'reverse-c'));
        $this->service->reverse($this->owner, $a, 'adjustment-correction', 'Correction', 'reverse-a');
        $this->service->reverse($this->owner, $c, 'confirmation-correction', 'Correction', 'reverse-c');
        $this->assertNull($this->service->overview()[0]['confirmed_owed']);
        $this->recordEntry('confirmation', ['net_amount' => '6']);
        $this->assertSame('6.000000', $this->service->overview()[0]['confirmed_owed']);
        $this->assertSame('8.000000', $c->fresh()->shares->first()->owed);
    }

    public function test_payments_require_correct_holder_currency_reference_and_record_actual_overpayments(): void
    {
        $this->recordEntry();
        foreach ([['amount' => '1', 'author_id' => 999], ['amount' => '1', 'author_id' => $this->author->id, 'currency' => 'EUR'], ['amount' => '1', 'author_id' => $this->author->id, 'reference' => ' ']] as $v) {
            $this->invalid(fn () => $this->recordEntry('payment', array_replace(['net_amount' => null], $v)));
        }
        $this->recordEntry('payment', ['net_amount' => null, 'author_id' => (string) $this->author->id, 'amount' => '8']);
        $this->assertSame('0.000000', $this->service->overview()[0]['payable']);
        $this->recordEntry('payment', ['net_amount' => null, 'author_id' => $this->author->id, 'amount' => '1']);
        $this->assertSame('-1.000000', $this->service->overview()[0]['payable']);
    }

    public function test_currency_totals_are_separate_and_no_conversion_is_performed(): void
    {
        $this->recordEntry();
        $p = Purchase::create(['collection_id' => $this->book->id, 'store' => 'PLAY_STORE', 'environment' => 'PRODUCTION', 'transaction_id' => 'GPA.eur-synthetic', 'product_id' => $this->book->product_id, 'purchased_at' => now()]);
        $l = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'sale:'.$p->id, 'status' => 'sale', 'currency' => 'EUR', 'amount' => '20', 'occurred_at' => now(), 'agreement_snapshot' => $this->sale->agreement_snapshot]);
        $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id, 'currency' => 'EUR', 'net_amount' => '16']);
        $r = collect($this->service->overview())->keyBy('currency');
        $this->assertCount(2, $r);
        $this->assertSame('8.000000', $r['USD']['confirmed_owed']);
        $this->assertSame('16.000000', $r['EUR']['confirmed_owed']);
    }

    public function test_decimal_multi_holder_gross_calculation_and_rounding_are_exact(): void
    {
        $this->assertSame(333333, Decimal::share(1000000, '33.3333'));
        $this->assertSame(-1, Decimal::share(-1, '50'));
        $this->assertSame('999999999999.999999', Decimal::text(Decimal::units('999999999999.999999')));
        $this->invalid(fn () => Decimal::units('1e3'));
        $this->invalid(fn () => Decimal::units('0.0000001'));
        $other = Author::create(['name' => 'Second synthetic']);
        $snapshot = array_replace($this->sale->agreement_snapshot, ['basis' => 'gross', 'contributors' => [['author_id' => $this->author->id, 'percentage' => 60], ['author_id' => $other->id, 'percentage' => 40]]]);
        $l = SalesLedger::create(['purchase_id' => $this->sale->purchase_id, 'entry_key' => 'gross-synthetic', 'status' => 'adjustment', 'currency' => 'USD', 'amount' => '10', 'occurred_at' => now(), 'agreement_snapshot' => $snapshot]);
        $e = $this->recordEntry('confirmation', ['sales_ledger_id' => $l->id, 'net_amount' => null]);
        $s = $e->shares->keyBy('author_id');
        $this->assertSame('6.000000', $s[$this->author->id]->owed);
        $this->assertSame('4.000000', $s[$other->id]->owed);
    }

    public function test_admin_authorization_owner_routes_and_form_posting(): void
    {
        $this->get(AccountingEntryResource::getUrl())->assertOk()->assertSee('No accounting records yet');
        $this->get(AuthorBalances::getUrl())->assertOk()->assertSee('Unknown')->assertSee('Record financial entry');
        Livewire::test(CreateAccountingEntry::class)->fillForm($this->data())->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseCount('accounting_entries', 1);
        $this->get(AccountingEntryResource::getUrl())->assertOk()->assertSee('Synthetic rights holder');
        $non = User::factory()->create(['is_owner' => false]);
        $this->actingAs($non);
        $this->get(AccountingEntryResource::getUrl())->assertForbidden();
        $this->get(AuthorBalances::getUrl())->assertForbidden();
        try {
            $this->service->record($non, $this->data());
            $this->fail('Non-owner posted');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertDatabaseCount('accounting_entries', 1);
    }

    public function test_immutable_history_and_empty_schema_rollback_are_protected(): void
    {
        $m = require database_path('migrations/2026_10_02_060000_create_accounting_journal.php');
        $m->down();
        $m->up();
        $e = $this->recordEntry();
        try {
            $e->update(['net_amount' => '999']);
            $this->fail('Changed history');
        } catch (\LogicException) {
        }
        try {
            $e->shares->first()->delete();
            $this->fail('Deleted share');
        } catch (\LogicException) {
        }
        try {
            $m->down();
            $this->fail('Erased history');
        } catch (\RuntimeException) {
        }
        $this->assertSame('8.000000', $e->fresh()->net_amount);
        $this->assertDatabaseCount('accounting_entries', 1);
    }

    public function test_staging_simulation_restores_all_history_and_production_simulation_is_refused(): void
    {
        $this->artisan('shelf:check-accounting')->assertSuccessful();
        config(['staging.testing' => true]);
        $this->artisan('shelf:check-accounting', ['--simulate-after-backup' => true])->assertSuccessful();
        $this->assertDatabaseCount('accounting_entries', 0);
        $this->assertDatabaseCount('purchases', 1);
    }

    public function test_sale_ledger_and_journal_filters_use_historical_rights_holder_and_book(): void
    {
        $this->recordEntry();
        Livewire::test(ListSales::class)->filterTable('rights_holder', (string) $this->author->id)->assertCanSeeTableRecords([$this->sale]);
        Livewire::test(ListAccountingEntries::class)->filterTable('book', (string) $this->book->id)->assertCanSeeTableRecords(AccountingEntry::all());
    }

    public function test_wrongly_marked_test_journal_never_counts_in_default_shares_or_balances(): void
    {
        $p = Purchase::create(['collection_id' => $this->book->id, 'store' => 'PLAY_STORE', 'environment' => 'SANDBOX', 'transaction_id' => 'GPA.exclusion-synthetic', 'product_id' => $this->book->product_id, 'purchased_at' => now()]);
        $l = SalesLedger::create(['purchase_id' => $p->id, 'entry_key' => 'sale:'.$p->id, 'status' => 'sale', 'currency' => 'USD', 'amount' => '999', 'occurred_at' => now(), 'agreement_snapshot' => $this->sale->agreement_snapshot]);
        $entry = AccountingEntry::create(['sales_ledger_id' => $l->id, 'kind' => 'confirmation', 'currency' => 'USD', 'reference' => 'synthetic', 'note' => 'Deliberately inconsistent synthetic fixture', 'occurred_at' => now(), 'created_by' => $this->owner->id, 'request_key' => 'synthetic-exclusion', 'source_key' => hash('sha256', 'synthetic-exclusion'), 'payload_hash' => hash('sha256', 'synthetic-exclusion'), 'agreement_snapshot' => $l->agreement_snapshot]);
        AccountingShare::create(['accounting_entry_id' => $entry->id, 'author_id' => $this->author->id, 'percentage' => 100, 'owed' => '999', 'paid' => '999']);
        $this->assertSame(0, AccountingEntry::count());
        $this->assertEquals(0, AccountingShare::sum('owed'));
        $this->assertEquals(0, AccountingShare::sum('paid'));
        $this->assertCount(1, $this->service->overview());
        $this->assertNull($this->service->overview()[0]['confirmed_owed']);
    }

    public function test_owner_payment_form_and_dated_reversal_action_work_without_sending_money(): void
    {
        $this->recordEntry();
        Livewire::test(CreateAccountingEntry::class)->fillForm($this->data('payment', ['net_amount' => null, 'author_id' => $this->author->id, 'amount' => '2']))->call('create')->assertHasNoFormErrors();
        $payment = AccountingEntry::where('kind', 'payment')->sole();
        $date = now()->subMinutes(10)->toDateTimeString();
        Livewire::test(ListAccountingEntries::class)
            ->callTableAction('reverse', $payment, data: ['request_key' => 'dated-reversal', 'occurred_at' => $date, 'reference' => 'synthetic-bank-return', 'note' => 'Bank returned the transfer'])
            ->assertHasNoErrors();
        $reversal = AccountingEntry::where('kind', 'payment_reversal')->sole();
        $this->assertSame($date, $reversal->occurred_at->toDateTimeString());
        $this->assertSame('0.000000', $this->service->overview()[0]['paid']);
    }
}
