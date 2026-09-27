<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    protected $fillable = [
        'language', 'title', 'slug', 'subtitle', 'description', 'author', 'dedication', 'introduction',
        'foreword_author', 'foreword', 'publication_info', 'cover_image', 'sort_order', 'is_active', 'product_id',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(function (Collection $collection): void {
            if ($collection->wasRecentlyCreated || $collection->wasChanged()) {
                AppSetting::query()->where('key', 'content_version')->increment('value');
            }
        });

        static::deleted(function (): void {
            AppSetting::query()->where('key', 'content_version')->increment('value');
        });
    }

    public function credits(): HasMany
    {
        return $this->hasMany(BookCredit::class)->orderBy('position')->orderBy('id');
    }

    public function assertPublishable(): void
    {
        $errors = [];
        if (! array_key_exists($this->language ?? '', config('books.languages'))) {
            $errors['language'] = 'Choose a supported language before publishing.';
        }
        if (! $this->credits()->where('role', 'author')->exists()) {
            $errors['credits'] = 'Add at least one author before publishing.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class)->orderBy('sort_order');
    }
}
