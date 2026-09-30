<?php

namespace App\Models;

class SalesLedger extends ImmutableRecord
{
    protected $table = 'sales_ledger';
    protected function casts(): array { return ['agreement_snapshot' => 'array', 'estimated_earnings' => 'array', 'amount' => 'decimal:6', 'occurred_at' => 'datetime']; }
    public function purchase() { return $this->belongsTo(Purchase::class); }
}
