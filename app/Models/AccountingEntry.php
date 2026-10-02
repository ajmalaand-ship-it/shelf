<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class AccountingEntry extends ImmutableRecord
{
    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('real_income', fn (Builder $q) => $q->whereHas('ledger.purchase', fn ($p) => $p->where('environment', 'PRODUCTION')));
    }

    public function scopeWithTestPurchases(Builder $q): Builder
    {
        return $q->withoutGlobalScope('real_income');
    }

    protected function casts(): array
    {
        return ['agreement_snapshot' => 'array', 'occurred_at' => 'datetime', 'gross_amount' => 'decimal:6', 'fees_amount' => 'decimal:6', 'taxes_amount' => 'decimal:6', 'net_amount' => 'decimal:6', 'basis_amount' => 'decimal:6'];
    }

    public function ledger()
    {
        return $this->belongsTo(SalesLedger::class, 'sales_ledger_id')->withTestPurchases();
    }

    public function shares()
    {
        return $this->hasMany(AccountingShare::class);
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reverses_id')->withTestPurchases();
    }

    public function original()
    {
        return $this->belongsTo(self::class, 'reverses_id')->withTestPurchases();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
