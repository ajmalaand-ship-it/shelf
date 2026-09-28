<?php

namespace App\Models;

use App\Support\UniqueSlug;
use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'book_type', 'language', 'title', 'slug', 'subtitle', 'description', 'author', 'dedication', 'introduction',
        'foreword_author', 'foreword', 'publication_info', 'cover_image', 'sort_order', 'is_active', 'product_id',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Collection $book): void {
            if (! $book->exists && blank($book->slug)) {
                $book->slug = UniqueSlug::for($book, $book->title, 'book');
            }
            if (! in_array($book->book_type ?? 'poetry', ['poetry', 'prose'], true)) {
                throw ValidationException::withMessages(['book_type' => 'Choose poetry or prose.']);
            }
            if (! $book->exists) {
                $book->created_by = auth()->id();
            }
            $book->updated_by = auth()->id();
        });
        static::deleting(function (Collection $book): void {
            if ($book->isForceDeleting() && $book->poems()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['book' => 'A book with content cannot be permanently deleted.']);
            }
            $book->forceFill(['updated_by' => auth()->id(), 'updated_at' => now()])->saveQuietly();
        });
        static::restored(fn (Collection $book) => $book->recordChange());
        static::saved(function (Collection $collection): void {
            if ($collection->wasRecentlyCreated || $collection->wasChanged()) {
                AppSetting::query()->where('key', 'content_version')->increment('value');
            }
        });

        static::deleted(function (): void {
            AppSetting::query()->where('key', 'content_version')->increment('value');
        });
    }

    public function recordChange(): void
    {
        $this->forceFill(['updated_by' => auth()->id(), 'updated_at' => now()])->saveQuietly();
        AppSetting::where('key', 'content_version')->increment('value');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->using(BookCategory::class)->orderBy('sort_order')->orderBy('categories.id');
    }

    public function getSelectorLabelAttribute(): string
    {
        $author = $this->credits->firstWhere('role', 'author')?->author?->name;

        return $this->title.($author ? ' — '.$author : '');
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
