<?php

namespace App\Models;

class PurchaseEvent extends ImmutableRecord
{
    protected function casts(): array { return ['details' => 'array', 'occurred_at' => 'datetime', 'event_timestamp_ms' => 'integer']; }
}
