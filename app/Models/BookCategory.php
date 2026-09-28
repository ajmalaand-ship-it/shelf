<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookCategory extends Pivot
{
    protected static function booted(): void
    {
        static::saved(fn (BookCategory $pivot) => Collection::withTrashed()->find($pivot->collection_id)?->recordChange());
        static::deleted(fn (BookCategory $pivot) => Collection::withTrashed()->find($pivot->collection_id)?->recordChange());
    }
}
