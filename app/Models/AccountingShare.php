<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class AccountingShare extends ImmutableRecord
{
    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('real_income', fn (Builder $q) => $q->whereHas('entry'));
    }

    protected function casts(): array
    {
        return ['estimated' => 'decimal:6', 'owed' => 'decimal:6', 'paid' => 'decimal:6', 'percentage' => 'decimal:4'];
    }

    public function entry()
    {
        return $this->belongsTo(AccountingEntry::class, 'accounting_entry_id');
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
