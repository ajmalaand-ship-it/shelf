<?php

namespace App\Support;

use App\Models\Collection;
use App\Models\Poem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookContentOrder
{
    public static function reorder(int $bookId, array $ids): void
    {
        DB::transaction(function () use ($bookId, $ids): void {
            $book = Collection::whereKey($bookId)->lockForUpdate()->firstOrFail();
            $items = Poem::withTrashed()->where('collection_id', $bookId)->orderBy('sort_order')->get();
            $expected = $items->whereNull('deleted_at')->modelKeys();
            $ids = array_map('intval', $ids);
            if (count($ids) !== count(array_unique($ids)) || count($ids) !== count($expected) || array_diff($ids, $expected)) {
                throw ValidationException::withMessages(['order' => 'Reorder all content of one book. Clear search and other filters first.']);
            }
            $ordered = array_merge($ids, $items->whereNotNull('deleted_at')->modelKeys());
            $offset = (int) $items->max('sort_order') + count($ordered) + 1;
            foreach ($ordered as $index => $id) {
                DB::table('poems')->where('id', $id)->update(['sort_order' => $offset + $index]);
            }
            foreach ($ordered as $index => $id) {
                DB::table('poems')->where('id', $id)->update(['sort_order' => $index + 1, 'updated_at' => now()]);
            }
            $book->recordChange();
        });
    }
}
