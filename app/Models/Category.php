<?php

namespace App\Models;

use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (! $category->exists && blank($category->slug)) {
                $category->slug = UniqueSlug::for($category, $category->name, 'category');
            }
            if ($category->exists && $category->isDirty('slug')) {
                throw ValidationException::withMessages(['slug' => 'Category identifiers are permanent.']);
            }
        });
        static::saved(fn () => AppSetting::where('key', 'content_version')->increment('value'));
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }
}
