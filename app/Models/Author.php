<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Author extends Model
{
    protected $fillable = ['slug', 'name', 'name_latin', 'biography', 'image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Author $author): void {
            if (! $author->exists && blank($author->slug)) {
                $author->slug = 'author-'.strtolower((string) Str::ulid());
            }
            if ($author->exists && $author->isDirty('slug')) {
                throw ValidationException::withMessages(['slug' => 'Author slugs are permanent.']);
            }
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $author->slug ?? '')) {
                throw ValidationException::withMessages(['slug' => 'Use lowercase letters, digits, and hyphens.']);
            }
            if ($author->image_path && ! self::safeImagePath($author->image_path)) {
                throw ValidationException::withMessages(['image_path' => 'Invalid author image path.']);
            }
        });
        static::saved(fn () => AppSetting::where('key', 'content_version')->increment('value'));
        static::deleted(fn () => AppSetting::where('key', 'content_version')->increment('value'));
    }

    public static function safeImagePath(string $path): bool
    {
        return (bool) preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9_\/.\-]*\.(?:jpg|jpeg|png|webp)\z/i', $path)
            && ! in_array('..', explode('/', $path), true)
            && ! in_array('.', explode('/', $path), true);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(BookCredit::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_author')
            ->withPivot(['role', 'position'])->withTimestamps();
    }
}
