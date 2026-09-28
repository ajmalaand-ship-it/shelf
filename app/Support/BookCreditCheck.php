<?php

namespace App\Support;

use App\Models\Collection;
use Illuminate\Support\Facades\DB;

class BookCreditCheck
{
    public static function report(): array
    {
        $books = Collection::with('credits.author')->orderBy('id')->get();
        $valid = $books->pluck('id')->all() === [3, 4, 5, 6, 7, 8];
        $rows = [];
        foreach ($books as $book) {
            $credits = $book->credits->map(fn ($credit) => [
                'role' => $credit->role, 'position' => $credit->position,
                'name' => $credit->author?->name,
            ])->all();
            $expected = $book->id === 6 ? [
                ['role' => 'author', 'position' => 1, 'name' => 'پروین پژواک'],
                ['role' => 'translator', 'position' => 2, 'name' => 'اجمل اند'],
            ] : [['role' => 'author', 'position' => 1, 'name' => 'اجمل اند']];
            $valid = $valid && $book->language === 'ps' && $credits === $expected;
            $rows[] = ['id' => $book->id, 'title' => $book->title,
                'language' => $book->language, 'credits' => $credits];
        }
        $unknown = DB::table('poems')->where('collection_id', 8)->orderBy('id')->get()
            ->filter(fn ($poem) => in_array(trim($poem->original_author ?? ''), ['', 'نوم نه دی ښودل شوی'], true))
            ->map(fn ($poem) => ['id' => $poem->id, 'title' => $poem->title,
                'work_type' => $poem->work_type, 'original_author' => $poem->original_author,
                'translator' => $poem->translator])->values()->all();

        return ['valid' => $valid, 'books' => $rows, 'book_8_unknown_author_unchanged' => $unknown];
    }
}
