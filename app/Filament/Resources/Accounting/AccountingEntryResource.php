<?php

namespace App\Filament\Resources\Accounting;

use App\Models\AccountingEntry;
use App\Models\Author;
use App\Models\Collection;
use App\Models\SalesLedger;
use App\Services\Accounting\AccountingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class AccountingEntryResource extends Resource
{
    protected static ?string $model = AccountingEntry::class;

    protected static ?string $slug = 'accounting-journal';

    protected static ?string $navigationLabel = 'Accounting journal';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_owner;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTestPurchases()->with(['ledger.purchase.book', 'shares.author', 'reversal', 'owner'])->when(ctype_digit((string) request('sales_ledger')), fn ($q) => $q->where('sales_ledger_id', request('sales_ledger')));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('request_key')->default(fn () => (string) Str::uuid()),
            Select::make('sales_ledger_id')->label('Verified sale or refund')->options(fn () => SalesLedger::with('purchase.book')->whereIn('status', ['sale', 'refund', 'adjustment'])->orderByDesc('id')->get()->mapWithKeys(fn ($l) => [$l->id => $l->purchase->book?->title.' — '.$l->status.' — '.($l->currency ?? 'Unknown currency').' '.($l->amount ?? 'Unknown amount').' — '.$l->purchase->transaction_id]))->searchable()->required()
                ->helperText('Only real verified transactions can receive financial records. Test purchases remain visible in Sales ledger and never create income or payments.'),
            Select::make('kind')->label('Record')->options(['estimate' => 'Estimate (provisional only)', 'confirmation' => 'Confirm financial figures', 'adjustment' => 'Adjustment to confirmed share basis', 'payment' => 'Payment already made (record only)'])->required()->live(),
            TextInput::make('currency')->label('Currency')->required()->maxLength(3)->helperText('Three uppercase letters, matching the source transaction. No currency conversion.'),
            TextInput::make('net_amount')->label('Net receipts')->visible(fn ($get) => in_array($get('kind'), ['estimate', 'confirmation']))->helperText('Required for a net agreement. Enter the evidenced amount received, or reversed for a refund. Blank means unknown; zero means explicitly known zero.'),
            TextInput::make('fees_amount')->label('Store fees')->visible(fn ($get) => in_array($get('kind'), ['estimate', 'confirmation']))->helperText('Blank means unknown. Do not enter zero unless the supporting record confirms zero.'),
            TextInput::make('taxes_amount')->label('Taxes')->visible(fn ($get) => in_array($get('kind'), ['estimate', 'confirmation']))->helperText('Blank means unknown. Enter signed deductions as shown by the source report. Net receipts are positive for sales and negative for refunds.'),
            TextInput::make('amount')->label('Amount')->visible(fn ($get) => in_array($get('kind'), ['adjustment', 'payment']))->helperText('Adjustment: signed change to the historical agreement calculation basis. Payment: positive amount actually paid. Unknown owed amounts and any overpayment remain visible in Author balances.'),
            Select::make('author_id')->label('Rights holder')->options(fn () => Author::pluck('name', 'id'))->searchable()->visible(fn ($get) => $get('kind') === 'payment')->required(fn ($get) => $get('kind') === 'payment'),
            DateTimePicker::make('occurred_at')->label('Record or payment date')->default(now())->maxDate(now())->required(),
            TextInput::make('reference')->label('Supporting reference')->maxLength(160)->required()->helperText('Store settlement/report reference, correction reference or bank/payment reference. Reusing a reference cannot duplicate the same entry.'),
            Textarea::make('note')->label('Explanation')->required()->maxLength(4000)->helperText('Describe the evidence and any adjustment. This form records history; it sends no money and changes no reader access.'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        $figures = fn ($record, $field) => $record->ledger->is_test ? 'Test — no income' : $record->shares->filter(fn ($s) => $s->$field !== null)->map(fn ($s) => $s->author?->name.': '.$s->$field)->implode('; ');

        return $table->columns([
            TextColumn::make('id')->label('Entry')->sortable(),
            TextColumn::make('ledger.purchase.book.title')->label('Book')->searchable()->wrap(),
            TextColumn::make('ledger.purchase.transaction_id')->label('Store transaction')->searchable()->toggleable(),
            TextColumn::make('ledger.status')->label('Source sale / refund')->badge(),
            TextColumn::make('kind')->label('Status')->badge(),
            TextColumn::make('mode')->getStateUsing(fn ($record) => $record->ledger->income_mode)->badge(),
            TextColumn::make('occurred_at')->label('Date')->dateTime()->sortable(), TextColumn::make('currency'),
            TextColumn::make('basis_amount')->label('Agreement basis amount')->placeholder('Unknown'),
            TextColumn::make('net_amount')->label('Net receipts')->placeholder('Unknown'),
            TextColumn::make('fees_amount')->label('Store fees')->placeholder('Unknown')->toggleable(),
            TextColumn::make('taxes_amount')->label('Taxes')->placeholder('Unknown')->toggleable(),
            TextColumn::make('estimated')->label('Estimated earnings')->getStateUsing(fn ($record) => $figures($record, 'estimated'))->placeholder('Not recorded')->wrap(),
            TextColumn::make('owed')->label('Confirmed owed')->getStateUsing(fn ($record) => $figures($record, 'owed'))->placeholder('Not recorded')->wrap(),
            TextColumn::make('paid')->label('Payments recorded')->getStateUsing(fn ($record) => $figures($record, 'paid'))->placeholder('Not recorded')->wrap(),
            TextColumn::make('reference')->label('Supporting reference')->searchable()->wrap(),
            TextColumn::make('note')->label('Explanation')->wrap()->toggleable(),
            TextColumn::make('agreement_snapshot.id')->label('Agreement version')->toggleable(),
            TextColumn::make('adjusts_id')->label('Adjusts confirmation')->placeholder('None')->toggleable(),
            TextColumn::make('reverses_id')->label('Reverses entry')->placeholder('None')->toggleable(),
            TextColumn::make('reversal.id')->label('Reversed by entry')->placeholder('None')->toggleable(),
            TextColumn::make('owner.name')->label('Recorded by')->toggleable(),
        ])->filters([
            SelectFilter::make('book')->options(fn () => Collection::withTrashed()->pluck('title', 'id'))->query(fn (Builder $query, $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('ledger.purchase', fn ($p) => $p->where('collection_id', $v)))),
            SelectFilter::make('rights_holder')->options(fn () => Author::pluck('name', 'id'))->query(fn (Builder $query, $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('shares', fn ($s) => $s->where('author_id', $v)))),
            SelectFilter::make('currency')->options(fn () => AccountingEntry::distinct()->pluck('currency', 'currency')),
            SelectFilter::make('kind')->options(['estimate' => 'Estimate', 'confirmation' => 'Confirmed figures', 'adjustment' => 'Adjustment', 'payment' => 'Payment', 'reversal' => 'Correction reversal', 'payment_reversal' => 'Payment reversal']),
        ])->recordActions([
            Action::make('reverse')->label('Reverse entry')->color('danger')->visible(fn ($record) => ! $record->reversal && ! in_array($record->kind, ['reversal', 'payment_reversal']))->requiresConfirmation()
                ->modalDescription('Adds a linked opposite entry. Original figures stay in history. No money is sent and reader access is unchanged.')
                ->schema([Hidden::make('request_key')->default(fn () => (string) Str::uuid()), DateTimePicker::make('occurred_at')->label('Reversal date')->default(now())->maxDate(now())->required(), TextInput::make('reference')->label('Reversal reference')->required()->maxLength(160), Textarea::make('note')->label('Reason')->required()->maxLength(4000)])
                ->action(fn ($record, $data) => app(AccountingService::class)->reverse(auth()->user(), $record, $data['reference'], $data['note'], $data['request_key'], $data['occurred_at'])),
        ])->defaultSort('id', 'desc')->emptyStateHeading('No accounting records yet')->emptyStateDescription('Use Record financial entry for a real verified sale. Test purchases never create income or payable balances.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAccountingEntries::route('/'), 'create' => Pages\CreateAccountingEntry::route('/create')];
    }
}
