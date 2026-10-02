<?php

namespace App\Models;

class PlayRefundRun extends ImmutableRecord
{
    protected function casts(): array { return ['started_at' => 'datetime', 'finished_at' => 'datetime']; }
}
