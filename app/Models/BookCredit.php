<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class BookCredit extends Model
{
    public const ROLES = ['author' => 'Author', 'translator' => 'Translator', 'editor' => 'Editor'];

    protected $table = 'collection_author';

    protected $fillable = ['collection_id', 'author_id', 'role', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (BookCredit $credit): void {
            if (! array_key_exists($credit->role, self::ROLES)) {
                throw ValidationException::withMessages(['role' => 'Invalid book credit role.']);
            }
        });
        static::saved(fn () => AppSetting::where('key', 'content_version')->increment('value'));
        static::deleted(fn () => AppSetting::where('key', 'content_version')->increment('value'));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
