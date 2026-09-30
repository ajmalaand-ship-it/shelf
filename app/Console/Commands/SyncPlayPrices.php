<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\PlayProductSync;
use App\Services\Play\GooglePlayClient;
use App\Services\Play\PlayPriceSync;
use Illuminate\Console\Command;

class SyncPlayPrices extends Command
{
    protected $signature = 'shelf:sync-play-prices {book? : Book ID; omit for all books} {--pending : Dispatch only due pending/error work} {--status : Read status only}';

    protected $description = 'Queue Google Play products from admin prices; never accepts or changes a price.';

    public function handle(PlayPriceSync $sync): int
    {
        if ($this->argument('book') && ! ctype_digit($this->argument('book'))) {
            $this->error('Use a numeric book ID.');

            return self::INVALID;
        }
        $books = Collection::withTrashed()->when($this->argument('book'), fn ($q, $id) => $q->whereKey($id))->orderBy('id');
        if ($this->option('status')) {
            foreach ($books->get() as $book) {
                $state = PlayProductSync::where('collection_id', $book->id)->first();
                $this->line('Book '.$book->id.': USD '.($book->price_usd ?? 'unset').' / '.ucfirst($state?->status ?? 'pending').' / '.($state?->message ?? 'Save the book to prepare sync.'));
            }

            return self::SUCCESS;
        }
        if (! GooglePlayClient::configured()) {
            $this->info('Disabled: waiting for the new Shelf service-account credentials. No Google API requests made.');

            return self::SUCCESS;
        }
        foreach ($books->get() as $book) {
            if ($this->option('pending')) {
                $sync->enqueue($book->id);
            } else {
                $sync->request($book);
            }
        }
        $this->info('Due book settings queued. Prices are changed only in the admin.');

        return self::SUCCESS;
    }
}
