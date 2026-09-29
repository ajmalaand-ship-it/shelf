<?php

namespace App\Support;

class SampleText
{
    /** Byte offsets preserve every original Unicode character and line ending. */
    public static function boundaries(string $body, string $unit): array
    {
        $break = '(?>\r\n|[\r\n\x{0085}\x{2028}\x{2029}])';
        $pattern = $unit === 'lines' ? $break : $break.'(?:[\t ]*'.$break.')+';
        preg_match_all('/'.$pattern.'/u', $body, $matches, PREG_OFFSET_CAPTURE);

        return array_column($matches[0], 1);
    }

    public static function prefix(string $body, string $unit, int $count): ?string
    {
        if (! in_array($unit, ['lines', 'paragraphs'], true) || $count < 1) {
            return null;
        }
        $boundaries = self::boundaries($body, $unit);
        // A partial sample must stop before the end; choose Full item otherwise.
        if (! isset($boundaries[$count - 1])) {
            return null;
        }
        if (! preg_match('/\S/u', substr($body, $boundaries[$count - 1]))) {
            return null;
        }

        return substr($body, 0, $boundaries[$count - 1]);
    }
}
