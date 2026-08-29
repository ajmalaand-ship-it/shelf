<?php

namespace App\Models;

use Database\Factories\PoemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poem extends Model
{
    /** @use HasFactory<PoemFactory> */
    use HasFactory;

    protected $fillable = [
        'collection_id', 'title', 'body', 'excerpt', 'work_type', 'original_author', 'translator',
        'source_date_place', 'source_note', 'audio_path', 'audio_duration_seconds', 'sort_order',
        'is_free_sample', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_free_sample' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'audio_duration_seconds' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(function (Poem $poem): void {
            $audioWasAddedOnCreate = $poem->wasRecentlyCreated && filled($poem->audio_path);
            if ($audioWasAddedOnCreate || $poem->wasChanged('audio_path')) {
                AppSetting::query()->where('key', 'content_version')->increment('value');
            }
        });
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function getAdminDisplayTitleAttribute(): string
    {
        return $this->title ?: str($this->body)->before("\n")->limit(80)->toString();
    }

    public function audioCacheKey(): ?string
    {
        return $this->audio_path ? hash('sha256', $this->audio_path) : null;
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
