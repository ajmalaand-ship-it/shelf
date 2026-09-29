<?php

namespace App\Models;

use App\Support\UniqueSlug;
use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    use SoftDeletes;

    public const STATUSES = ['draft' => 'Draft', 'ready' => 'Ready for review', 'published' => 'Published', 'withdrawn' => 'Withdrawn'];

    protected $attributes = ['status' => 'draft'];

    private bool $recordStatusChange = false;

    private ?string $previousStatus = null;

    protected $fillable = [
        'book_type', 'language', 'title', 'slug', 'subtitle', 'description', 'author', 'dedication', 'introduction',
        'foreword_author', 'foreword', 'publication_info', 'cover_image', 'sort_order', 'status', 'product_id',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'status_changed_at' => 'datetime'];
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
            if (! array_key_exists($book->status, self::STATUSES)) {
                throw ValidationException::withMessages(['status' => 'Choose a valid publication status.']);
            }
            $book->recordStatusChange = $book->isDirty('status') || ! $book->exists;
            $book->previousStatus = $book->exists ? $book->getOriginal('status') : null;
            if ($book->recordStatusChange) {
                $book->status_changed_by = auth()->id();
                $book->status_changed_at = now();
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
            if ($collection->recordStatusChange) {
                DB::table('book_status_changes')->insert([
                    'collection_id' => $collection->id,
                    'from_status' => $collection->previousStatus,
                    'to_status' => $collection->status, 'changed_by' => auth()->id(), 'changed_at' => now(),
                ]);
                $collection->recordStatusChange = false;
            }
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
        if (! $this->cover_image || ! self::safeCoverPath($this->cover_image) || ! Storage::disk('covers')->exists($this->cover_image)) {
            $errors['cover_image'] = 'Add a cover before publishing.';
        }
        if (! $this->poems()->where('is_active', true)->exists()) {
            $errors['status'] = 'Add at least one visible item before publishing.';
        }
        if (! $this->poems()->where('is_active', true)->whereIn('sample_mode', ['full', 'partial'])->get()->contains(fn (Poem $item): bool => $item->hasSample())) {
            $errors['status'] = 'Approve at least one visible free sample before publishing.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function sampleSummary(): string
    {
        $items = $this->poems()->whereIn('sample_mode', ['full', 'partial'])->get();
        $visible = $items->where('is_active', true)->filter(fn (Poem $item): bool => $item->hasSample());

        return 'Visible samples: '.$visible->where('sample_mode', 'full')->count().' full items, '
            .$visible->where('sample_mode', 'partial')->count().' first parts. '
            .$items->where('is_active', false)->count().' hidden sample items. A visible sample is required to publish.';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && ! $this->trashed();
    }

    // Step 4 extension point: verified prior purchasers may read Withdrawn books.
    // Never use discovery visibility as purchase ownership, and never delete on withdrawal.
    public function allowsPriorPurchaserAccess(): bool
    {
        return false; // Fail closed until book-specific purchases are implemented.
    }

    public function changeStatus(string $status): void
    {
        DB::transaction(function () use ($status): void {
            $book = self::query()->lockForUpdate()->findOrFail($this->id);
            if ($status === 'published') {
                $book->assertPublishable();
            }
            $book->update(['status' => $status]);
        });
        $this->refresh();
    }

    public static function safeCoverPath(string $path): bool
    {
        return $path !== '' && ! str_starts_with($path, '/') && ! str_contains($path, '\\')
            && ! preg_match('~(^|/)\.\.?(/|$)|[\x00-\x1f]~', $path);
    }

    public function coverUrl(bool $ownerPreview = false): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        return $ownerPreview
            ? URL::temporarySignedRoute('owner-preview.books.cover', now()->addMinutes(10), ['collection' => $this->id])
            : route('books.cover', ['collection' => $this->id]);
    }

    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class)->orderBy('sort_order');
    }
}
