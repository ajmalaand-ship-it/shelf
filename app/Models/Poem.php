<?php

namespace App\Models;

use App\Support\UniqueSlug;
use Database\Factories\PoemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Poem extends Model
{
    /** @use HasFactory<PoemFactory> */
    use HasFactory;

    use SoftDeletes;

    public const LAYOUT_SOURCE = 'SOURCE';

    public const LAYOUT_COUPLET = 'COUPLET';

    public const LAYOUT_FOUR_LINES = 'FOUR_LINES';

    public const LAYOUT_MODES = [self::LAYOUT_SOURCE, self::LAYOUT_COUPLET, self::LAYOUT_FOUR_LINES];

    protected $fillable = [
        'slug', 'collection_id', 'title', 'body', 'excerpt', 'work_type', 'original_author', 'translator',
        'source_date_place', 'source_note', 'layout_mode', 'artwork_path', 'audio_path', 'audio_duration_seconds', 'sort_order',
        'is_free_sample', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_free_sample' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'audio_duration_seconds' => 'integer'];
    }

    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            $book = Collection::withTrashed()->whereKey($this->collection_id)->lockForUpdate()->firstOrFail();
            $this->setRelation('collection', $book);
            if (! $this->exists || $this->isDirty('collection_id')) {
                $lastPosition = (int) static::withTrashed()->where('collection_id', $this->collection_id)->max('sort_order');
                if (! isset($this->sort_order) || ($this->exists && $this->isDirty('collection_id')) || $this->sort_order <= $lastPosition) {
                    $this->sort_order = $lastPosition + 1;
                }
            }

            return parent::save($options);
        });
    }

    public function getContentLabelAttribute(): string
    {
        return $this->collection?->book_type === 'prose' ? 'Chapter' : 'Poem';
    }

    public function getEffectiveLayoutModeAttribute(): string
    {
        return $this->collection?->book_type === 'prose' ? self::LAYOUT_SOURCE : ($this->layout_mode ?? self::LAYOUT_SOURCE);
    }

    protected static function booted(): void
    {
        static::creating(function (Poem $poem): void {
            if (blank($poem->slug)) {
                $poem->slug = UniqueSlug::for($poem, $poem->title, 'content');
            }
        });
        static::saving(function (Poem $poem): void {
            if ($poem->isDirty('layout_mode') && $poem->collection?->book_type === 'prose') {
                $poem->layout_mode = self::LAYOUT_SOURCE;
            }
        });
        static::saved(function (Poem $poem): void {
            Collection::withTrashed()->find($poem->collection_id)?->recordChange();
            if ($poem->wasChanged('collection_id')) {
                Collection::withTrashed()->find($poem->getOriginal('collection_id'))?->recordChange();
            }
        });
        static::deleted(fn (Poem $poem) => Collection::withTrashed()->find($poem->collection_id)?->recordChange());
        static::restored(fn (Poem $poem) => Collection::withTrashed()->find($poem->collection_id)?->recordChange());

    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function getAdminDisplayTitleAttribute(): string
    {
        if (filled($this->title)) {
            return $this->title;
        }

        foreach (preg_split('/\R/u', $this->body) ?: [] as $line) {
            if (filled(trim($line))) {
                return Str::limit(trim($line), 80);
            }
        }

        return '';
    }

    public function audioCacheKey(): ?string
    {
        return $this->audio_path ? hash('sha256', $this->audio_path) : null;
    }

    public function artworkCacheKey(): ?string
    {
        return $this->artwork_path ? hash('sha256', $this->artwork_path) : null;
    }

    public function audioFormat(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        $extension = strtolower(pathinfo($this->audio_path, PATHINFO_EXTENSION));

        return in_array($extension, ['m4a', 'mp3', 'wav'], true) ? $extension : null;
    }
}
