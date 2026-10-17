<?php

namespace App\Models;

class FinancialRetentionRecord extends ImmutableRecord
{
    protected function casts(): array
    {
        return ['evidence' => 'array', 'created_at' => 'datetime'];
    }
}
