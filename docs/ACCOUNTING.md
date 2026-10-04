# Sales and author-share accounting — Step 4 / 2 October 2026

Approved scope: Master Record 6.9 and D10. Owner-only recordkeeping, no money
transfers or automatic payouts. Existing purchases, entitlements, refunds and
Library behavior remain unchanged; live purchasing remains sandbox-only.

## Where to find it

In **Admin → Sales and readers**:

- **Sales ledger**: verified source sales/refunds, transaction references, dates,
  amount/currency/status, Test labels and historical rights-holder/book filters.
  **Accounting history** opens the linked journal. Original financial snapshots
  remain immutable and are available as optional columns.
- **Accounting journal**: **Record financial entry** for a real verified source
  sale/refund. Choose Estimate, Confirm financial figures, Adjustment, or Payment
  already made. Filter by book, historical rights holder, currency and record type.
  Each entry retains supporting reference, explanation, date and recording owner.
- **Author balances**: totals by book, rights holder and currency. Three separate
  figures: provisional estimated earnings, confirmed owed and payments recorded;
  payable is confirmed owed less recorded payments only when all figures are known.

No real source sales exist from the owner's license-tester purchases. They stay
visible as Test in Sales ledger and cannot create accounting income, owed amounts
or payments. An empty real-accounting screen is expected until verified real
source transactions exist under a separately approved real-payment setup.

## How to record figures

1. Select the existing verified source entry; this form cannot invent a purchase.
2. Use its transaction currency. No currency conversion or mixed-currency totals.
3. For a net agreement, enter evidenced **net receipts**. Estimate means provisional;
   confirmation requires a supporting settlement/report reference. Blank fees/taxes
   remain unknown, never zero. A known net receipt can be recorded without inventing
   separate fees/taxes. Explicit zero means evidenced zero, not a missing amount.
4. The historical agreement snapshot determines the rights holders and percentages.
   D10: all six current books, 100% of net receipts to Ajmal from 1 October 2026.
   Later agreement versions never recalculate these entries or original snapshots.
   Missing/invalid historical agreements are held, not replaced by current terms.
5. Net receipts for sales are positive; reversed net receipts for refunds are negative.
   Enter signed fees/taxes exactly as the source report shows. The refund's gross
   amount does not prove the amount of net receipts reversed or fees returned.
6. A refund is already a separate linked Sales ledger entry. Confirm its financial
   figures separately. Until known, the full owed/payable balance remains Unknown,
   showing the known portion separately. Access revocation remains the existing
   purchase/refund mechanism and is not controlled by accounting confirmation.

## Adjustments, payments and corrections

An adjustment is a signed change to the calculation basis of a confirmed source
entry, linked to that confirmation and its historical agreement. For D10 this is
the change in net receipts, not an invented direct change to the author's percentage.
Document the supporting correction reference and reason. No access changes result.

Payment records require the rights holder, positive amount, transaction currency,
actual payment date and bank/payment reference. This records money **already paid**;
it sends nothing. A supported past payment may be recorded while earnings remain
unknown or after an overpayment; payable stays unknown when receipts are incomplete,
and an evidenced overpayment is shown as a negative balance requiring review.
No repayment or transfer is initiated automatically.

Use **Reverse entry** to correct figures or reverse a payment: enter reversal date,
reference and reason. The original remains; an exactly opposite linked entry is
added. Reverse active adjustments before replacing their underlying confirmation.
After reversing an estimate/confirmation, record the corrected figures with a new
reference. Do not reverse a reversal. Duplicate requests or reuse of the same
source/type/rights-holder reference cannot create a second posting; conflicting
figures under the same reference are rejected. Posting locks serialize each purchase.

## Precision and protection

Money uses integer arithmetic at six decimal places, never binary floating point.
Share calculations use the exact historical percentage and round to the nearest
micro-unit, half away from zero. No FX rate, new deduction or standard percentage
is invented. Original sale/refund/author history is never updated or erased.

Default AccountingEntry/AccountingShare queries include only explicitly PRODUCTION
source purchases. History explicitly opts into Test visibility without counting
Test values. Unknown environments fail closed. No accounting data goes to reader APIs.
Owner-only admin access and service authorization protect every recording action.

The migration adds accounting_entries and accounting_shares without modifying
existing rows. Empty tables can be rolled back. Once populated, schema rollback
refuses to erase history: roll back code while retaining accounting tables and
linked records; never restore a database over later reader or financial activity.

## Verification and deployment evidence

Focused verification runs through scripts/run_tests.sh using SHELF_PHP_TEST_FILTER.
It covers decimal/net/gross shares, unknown receipts, Test/unknown exclusion,
refunds, duplicates, corrections/payment reversals, historical agreement versions,
separate currencies, admin authorization and actual Livewire forms/actions/filters.
Existing purchase/refund tests are reused. No mobile files or phone tests are needed.

The established staging deployment runs the required complete PHP gate (no mobile
tests when mobile files are unchanged), plus shelf:check-accounting
--simulate-after-backup. It exercises D10-style confirmation, payment/reversal,
unknown refund receipts, duplicate protection, historical-rights-holder SQL and
Test exclusion using synthetic rows; all rows roll back and fingerprints must match.
Production promotion backs up first, preserves old reader/payment/agreement and
accounting fingerprints, migrates and runs concise read-only health checks.

The exact staged/deployed commit, passing test log hash and verified production
backup are recorded by scripts/staging/workflow.py in the private authoritative
release-state.json. Read them without secrets using:

```bash
python3 -B scripts/staging/workflow.py status
php artisan shelf:check-accounting
```

Current progress and completion evidence belong in Master Record Part 10 and the
status reconciliation. Step 4 is **not all complete**: second-phone restore remains
deferred to pre-release, NOT PASSED. Product/price sync's last HTTP 403 and disabled
runner remain unfinished; working refund API access does not prove price permissions.
Accounting deployed at 5ff131f, retained in deployed/pushed 5f75fa0.
Step 5 OneDrive remote isolated recovery passed 3 October (34 tables, 774 files);
first unattended run failed 4 October after upload. Actual alerts and replacement-
host recovery remain unverified. See OFFSERVER_BACKUP.md.
