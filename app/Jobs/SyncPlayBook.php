<?php

namespace App\Jobs;

use App\Services\Play\PlayPriceSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncPlayBook implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 1; // Durable outbox owns retries and retains the latest desired version.

    public function __construct(public int $bookId) {}

    public function handle(PlayPriceSync $sync): void
    {
        $sync->run($this->bookId);
    }
}
