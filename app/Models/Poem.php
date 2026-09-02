<?php

namespace App\Models;

use Database\Factories\PoemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Poem extends Model
{
    /** @use HasFactory<PoemFactory> */
    use HasFactory;

    public const LAYOUT_SOURCE = 'SOURCE';

    public const LAYOUT_COUPLET = 'COUPLET';

    public const LAYOUT_FOUR_LINES = 'FOUR_LINES';

    public const LAYOUT_MODES = [self::LAYOUT_SOURCE, self::LAYOUT_COUPLET, self::LAYOUT_FOUR_LINES];

    protected $fillable = [
        'collection_id', 'title', 'body', 'excerpt', 'work_type', 'original_author', 'translator',
        'source_date_place', 'source_note', 'layout_mode', 'artwork_path', 'audio_path', 'audio_duration_seconds', 'sort_order',
        'is_free_sample', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_free_sample' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'audio_duration_seconds' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(function (Poem $poem): void {
            if ($poem->wasRecentlyCreated || $poem->wasChanged()) {
                AppSetting::query()->where('key', 'content_version')->increment('value');
            }
        });

        static::deleted(function (): void {
            AppSetting::query()->where('key', 'content_version')->increment('value');
        });
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
