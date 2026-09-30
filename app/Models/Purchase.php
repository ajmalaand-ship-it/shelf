<?php

namespace App\Models;

class Purchase extends ImmutableRecord
{
    protected function casts(): array { return ['purchased_at' => 'datetime']; }
    public function book() { return $this->belongsTo(Collection::class, 'collection_id')->withTrashed(); }
    public function reader() { return $this->belongsTo(Reader::class); }
    public function entries() { return $this->hasMany(SalesLedger::class); }
}
