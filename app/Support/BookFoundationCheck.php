<?php

namespace App\Support;

use App\Models\Collection;
use App\Models\Poem;

class BookFoundationCheck
{
    public static function report(): array
    {
        $books = Collection::withCount(['categories', 'poems'])->orderBy('id')->get();
        $items = Poem::count();

        return [
            'books' => $books->map(fn ($book) => [
                'id' => $book->id, 'title' => $book->title, 'type' => $book->book_type,
                'language' => $book->language, 'category_count' => $book->categories_count,
                'content_count' => $book->poems_count,
            ])->all(),
            'total_content' => $items,
            'valid' => $books->modelKeys() === [3, 4, 5, 6, 7, 8]
                && $books->every(fn ($book) => $book->book_type === 'poetry' && $book->language === 'ps')
                && $items === 342 && (int) $books->sum('poems_count') === 342,
        ];
    }
}
