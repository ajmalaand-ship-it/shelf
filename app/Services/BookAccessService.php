<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Reader;
use Illuminate\Support\Facades\DB;

class BookAccessService
{
    public function ownsBook(?Reader $reader, Collection $book): bool
    {
        return $reader !== null && DB::table('book_entitlements')->where('reader_id', $reader->id)
            ->where('collection_id', $book->id)->where('active', true)->exists();
    }
}
