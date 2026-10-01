<?php

namespace App\Support;

final class Staging
{
    public static function active(): bool
    {
        return app()->environment('staging') || (app()->runningUnitTests()
            ? (bool) config('staging.testing', false) : is_file(base_path('.shelf-staging')));
    }

    public static function identity(int $reader): string
    {
        return (self::active() ? 'staging_' : '').$reader;
    }

    public static function readerId(string $identity): int
    {
        $pattern = self::active() ? '/^staging_([1-9][0-9]*)$/' : '/^([1-9][0-9]*)$/';
        abort_unless(preg_match($pattern, $identity, $match), 422, 'Purchase identity belongs to another environment.');
        return (int) $match[1];
    }
}
