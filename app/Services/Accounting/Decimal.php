<?php

namespace App\Services\Accounting;

use Illuminate\Validation\ValidationException;

/** Decimal money, six-place precision. No floating point or currency conversion. */
final class Decimal
{
    public static function units(mixed $v): int
    {
        if (! is_string($v) && ! is_int($v)) {
            throw ValidationException::withMessages(['amount' => 'Enter a plain decimal amount (up to six decimal places).']);
        }
        if (! preg_match('/^(-?)([0-9]{1,12})(?:\.([0-9]{1,6}))?$/D', (string) $v, $m)) {
            throw ValidationException::withMessages(['amount' => 'Enter a plain decimal amount (up to six decimal places).']);
        }
        $n = (int) $m[2] * 1000000 + (int) str_pad($m[3] ?? '', 6, '0');

        return $m[1] === '-' ? -$n : $n;
    }

    public static function text(int $v): string
    {
        return ($v < 0 ? '-' : '').intdiv(abs($v), 1000000).'.'.str_pad((string) (abs($v) % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function share(int $v, mixed $percentage): int
    {
        $p = self::units((string) $percentage); // percent expressed in millionths
        if ($p <= 0 || $p > 100000000) {
            throw ValidationException::withMessages(['agreement' => 'Invalid historical share percentage.']);
        }
        $n = abs($v);
        $whole = intdiv($n, 100000000) * $p;
        $rest = intdiv(($n % 100000000) * $p + 50000000, 100000000);

        return ($v < 0 ? -1 : 1) * ($whole + $rest); // nearest micro-unit, half away from zero
    }
}
