<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogueFilters
{
    public static function apply(Builder $query, Request $request): Builder
    {
        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'book_type' => ['sometimes', 'nullable', Rule::in(['poetry', 'prose'])],
        ]);
        if (filled($filters['q'] ?? null)) {
            // Bind the query and treat SQL wildcard characters as literal text.
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($filters['q'])).'%';
            $query->where(function (Builder $query) use ($term): void {
                $query->whereRaw("title LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("subtitle LIKE ? ESCAPE '!'", [$term])
                    ->orWhereHas('credits.author', fn (Builder $authors) => $authors
                        ->whereRaw("name LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("name_latin LIKE ? ESCAPE '!'", [$term]));
            });
        }

        return $query
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->whereHas('categories', fn ($categories) => $categories->where('slug', $filters['category'])))
            ->when(filled($filters['book_type'] ?? null), fn ($query) => $query->where('book_type', $filters['book_type']));
    }
}
