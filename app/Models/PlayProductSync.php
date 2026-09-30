<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayProductSync extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['revision' => 'integer', 'attempts' => 'integer', 'queued_at' => 'datetime',
            'next_attempt_at' => 'datetime', 'synced_at' => 'datetime', 'synced_price_usd' => 'decimal:2', 'synced_active' => 'boolean'];
    }
}
