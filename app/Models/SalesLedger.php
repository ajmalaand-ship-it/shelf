<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/** Ordinary ledger queries are real income only. History explicitly includes tests. */
class SalesLedger extends ImmutableRecord
{
    protected $table = 'sales_ledger';

    protected $appends = ['income_mode', 'is_test'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('real_income', fn (Builder $query) => $query
            ->whereHas('purchase', fn (Builder $purchase) => $purchase->where('environment', 'PRODUCTION')));
    }

    public function scopeWithTestPurchases(Builder $query): Builder
    {
        return $query->withoutGlobalScope('real_income');
    }

    public function scopeForRightsHolder(Builder $query, int $authorId): Builder
    {
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            return $query->whereRaw("EXISTS (SELECT 1 FROM json_each(sales_ledger.agreement_snapshot, '$.contributors') WHERE json_extract(json_each.value, '$.author_id') = ?)", [$authorId]);
        }

        return $query->whereJsonContains('agreement_snapshot->contributors', ['author_id' => $authorId]);
    }

    public function getIsTestAttribute(): bool
    {
        return $this->purchase?->environment !== 'PRODUCTION';
    }

    public function getIncomeModeAttribute(): string
    {
        return $this->is_test ? 'Test' : 'Real';
    }

    protected function casts(): array
    {
        return ['agreement_snapshot' => 'array', 'estimated_earnings' => 'array', 'amount' => 'decimal:6', 'occurred_at' => 'datetime'];
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
