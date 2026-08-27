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

    protected $fillable = ['collection_id', 'title', 'body', 'excerpt', 'audio_path', 'audio_duration_seconds', 'sort_order', 'is_free_sample', 'is_active'];

    protected function casts(): array
    {
        return ['is_free_sample' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'audio_duration_seconds' => 'integer'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
