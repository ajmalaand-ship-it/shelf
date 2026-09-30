<?php

namespace App\Models;

class BookPriceChange extends ImmutableRecord
{
    protected function casts(): array
    {
        return ['old_price_usd' => 'decimal:2', 'new_price_usd' => 'decimal:2'];
    }
}
