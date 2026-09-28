<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    public static function for(Model $model, ?string $title, string $fallback): string
    {
        $base = Str::limit(Str::slug($title ?? ''), 210, '') ?: $fallback;
        $slug = $base;
        $suffix = 1;
        while ($model->newQueryWithoutScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
