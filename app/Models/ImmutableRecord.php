<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ImmutableRecord extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('History is immutable; add a new entry.'));
        static::deleting(fn () => throw new \LogicException('History is retained.'));
    }
}
