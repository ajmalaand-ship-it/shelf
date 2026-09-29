<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordImport extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['imported_at' => 'datetime', 'item_ids' => 'array', 'warnings' => 'array', 'item_count' => 'integer', 'collection_id' => 'integer', 'imported_by' => 'integer'];
    }
}
