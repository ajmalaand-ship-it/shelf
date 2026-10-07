<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use App\Models\Author;
use App\Models\Collection;
use App\Models\SalesLedger;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = SalesLedger::class;

    protected static ?string $modelLabel = 'sales entry';

    protected static ?string $navigationLabel = 'Sales ledger';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Sales and readers';

    public static function canCreate(): bool
    {
        return false;
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
        return parent::getEloquentQuery()->withTestPurchases()->with(['purchase.book', 'purchase.reader'])
            ->when(ctype_digit((string) request('reader')), fn ($q) => $q->whereHas('purchase', fn ($p) => $p->where('reader_id', request('reader'))));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('purchase.id')->label('Purchase ID')->sortable(),
            TextColumn::make('purchase.book.title')->label('Book')->searchable(),
            TextColumn::make('purchase.reader.email')->label('Reader')->placeholder('Deleted account')->searchable(),
            TextColumn::make('purchase.transaction_id')->label('Store transaction')->searchable(),
            TextColumn::make('income_mode')->label('Mode')->badge()->color(fn ($state) => $state === 'Test' ? 'warning' : 'success'),
            TextColumn::make('occurred_at')->dateTime()->sortable(),
            TextColumn::make('status')->badge(), TextColumn::make('currency'), TextColumn::make('amount')->placeholder('Unknown'),
            TextColumn::make('fees_status')->label('Store fees'), TextColumn::make('taxes_status')->label('Taxes'),
            TextColumn::make('earnings_status')->label('Earnings')->getStateUsing(fn ($record) => $record->is_test ? 'Test — no income' : $record->earnings_status),
            TextColumn::make('rights_holders')->label('Rights holders')->getStateUsing(fn ($record) => collect($record->agreement_snapshot['contributors'] ?? [])->map(fn ($c) => Author::find($c['author_id'])?->name ?? 'Unknown')->implode(', '))->wrap(),
            TextColumn::make('agreement_snapshot.id')->label('Agreement version')->placeholder('Not agreed'),
            TextColumn::make('estimated_earnings')->label('Original estimated snapshot')->toggleable(isToggledHiddenByDefault: true)->getStateUsing(fn ($record) => $record->is_test ? 'Test — no income' : $record->estimated_earnings)->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state)->placeholder('Unknown')->wrap(),
            TextColumn::make('confirmed_owed')->label('Original owed snapshot')->toggleable(isToggledHiddenByDefault: true)->getStateUsing(fn ($record) => $record->is_test ? 'Test — no income' : $record->confirmed_owed)->placeholder('Unknown'), TextColumn::make('payments_made')->label('Original payment snapshot')->toggleable(isToggledHiddenByDefault: true)->getStateUsing(fn ($record) => $record->is_test ? 'Test — no income' : $record->payments_made)->placeholder('No payouts'),
        ])->filters([
            SelectFilter::make('book')->options(fn () => Collection::withTrashed()->pluck('title', 'id'))->query(fn (Builder $query, $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->whereHas('purchase', fn ($p) => $p->where('collection_id', $v)))),
            SelectFilter::make('rights_holder')->options(fn () => Author::pluck('name', 'id'))->query(fn (Builder $query, $data) => $query->when($data['value'] ?? null, fn ($q, $v) => $q->forRightsHolder((int) $v))),
            SelectFilter::make('status')->options(['sale' => 'Sale', 'refund' => 'Refund', 'revoke' => 'Revocation']),
            SelectFilter::make('currency')->options(fn () => SalesLedger::withTestPurchases()->whereNotNull('currency')->distinct()->pluck('currency', 'currency')->all()),
        ])->recordActions([
            Action::make('recovery_audit')->label('Recovery history')->modalSubmitAction(false)->modalCancelActionLabel('Close')
                ->modalContent(fn ($record) => view('admin.purchase-recovery-audit', [
                    'recoveries' => \App\Models\PurchaseRecovery::where('purchase_id', $record->purchase_id)->orderBy('id')->get(),
                ])),
            Action::make('accounting')->label('Accounting history')->url(fn ($record) => AccountingEntryResource::getUrl('index', ['sales_ledger' => $record->id]))])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSales::route('/')];
    }
}
