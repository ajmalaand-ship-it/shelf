<?php

namespace App\Services\Play;

use App\Jobs\SyncPlayBook;
use App\Models\Collection;
use App\Models\PlayProductSync;
use Illuminate\Support\Facades\DB;

class PlayPriceSync
{
    public function request(Collection $book): void
    {
        if (config('play_sync.deferred', true)) {
            return; // Preserve historical sync records; manual saves create no sync intent.
        }
        DB::transaction(function () use ($book): void {
            $sync = PlayProductSync::where('collection_id', $book->id)->lockForUpdate()->first();
            $values = ['status' => 'pending', 'message' => GooglePlayClient::configured()
                ? 'Waiting to send the latest book price and availability to Google Play.' : 'Waiting for Google Play setup.',
                'attempts' => 0, 'next_attempt_at' => null, 'queued_at' => null];
            if ($sync) {
                $sync->update(array_merge($values, ['revision' => $sync->revision + 1]));
            } else {
                PlayProductSync::create(array_merge($values, ['collection_id' => $book->id, 'revision' => 1]));
            }
            // Never publish a job for a rolled-back admin save.
            DB::afterCommit(fn () => $this->enqueue($book->id));
        });
    }

    public function enqueue(int $bookId): bool
    {
        if (! GooglePlayClient::configured()) {
            return false;
        }

        return DB::transaction(function () use ($bookId): bool {
            $sync = PlayProductSync::where('collection_id', $bookId)->lockForUpdate()->first();
            if (! $sync || $sync->status === 'synced' || $sync->next_attempt_at?->isFuture()
                || ($sync->queued_at && $sync->queued_at->gt(now()->subMinutes(5)))) {
                return false;
            }
            // Explicit durable connection: never run a remote sync inline on an admin save.
            SyncPlayBook::dispatch($bookId)->onConnection('database')->onQueue('play-prices')->afterCommit();
            $sync->update(['queued_at' => now()]);

            return true;
        });
    }

    public function run(int $bookId): void
    {
        if (config('play_sync.deferred', true)) {
            return; // Even a retained queued job must not retry or rewrite sync history.
        }
        // Lock in the same order as admin saves. A concurrent edit becomes a new pending
        // revision after this transaction, rather than being acknowledged by an old job.
        DB::transaction(function () use ($bookId): void {
            $book = Collection::withTrashed()->whereKey($bookId)->lockForUpdate()->first();
            $sync = PlayProductSync::where('collection_id', $bookId)->lockForUpdate()->first();
            if (! $book || ! $sync || $sync->status === 'synced' || $sync->next_attempt_at?->isFuture()) {
                return;
            }
            if (! GooglePlayClient::configured()) {
                $sync->update(['status' => 'pending', 'message' => 'Waiting for Google Play setup.', 'queued_at' => null]);

                return;
            }
            try {
                app(GooglePlayClient::class)->sync($book);
                $sync->update(['status' => 'synced', 'message' => $book->isPublished() && $book->price_usd !== null
                    ? 'Google Play has the latest price. This book is available to buy.'
                    : 'Google Play has the latest settings. New purchases are stopped.',
                    'synced_at' => now(), 'synced_price_usd' => $book->price_usd,
                    'synced_active' => $book->isPublished() && $book->price_usd !== null,
                    'attempts' => 0, 'next_attempt_at' => null, 'queued_at' => null]);
            } catch (\Throwable $error) {
                $attempts = $sync->attempts + 1;
                $delay = [60, 300, 900, 3600, 21600][min($attempts - 1, 4)];
                // Raw HTTP/key exceptions can contain credentials; store only our safe text.
                $sync->update(['status' => 'error', 'attempts' => $attempts, 'queued_at' => null,
                    'next_attempt_at' => now()->addSeconds($delay), 'message' => $error instanceof PlaySyncException
                        ? $error->getMessage() : 'Google Play could not be reached. Shelf will retry automatically.']);
            }
        });
    }
}
