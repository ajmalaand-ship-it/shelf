<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    protected $fillable = ['title', 'slug', 'subtitle', 'description', 'cover_image', 'sort_order', 'is_active', 'product_id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class)->orderBy('sort_order');
    }
}
