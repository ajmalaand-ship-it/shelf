<?php

namespace App\Support;

use App\Models\Poem;

final class PoetryPresentation
{
    /** @return list<array{text: string, gap_after_em: float}> */
    public static function blocks(string $body, string $layoutMode): array
    {
        if (preg_match('/\n(?:[\t ]*\n)+/', $body) === 1 || $layoutMode === Poem::LAYOUT_SOURCE) {
            return self::paragraphBlocks($body);
        }
        if ($layoutMode === Poem::LAYOUT_COUPLET) {
            return self::legacyGroupedBlocks($body, 2, 0.5);
        }

        return self::legacyGroupedBlocks($body, 4, 1.1);
    }

    /** @return list<array{text: string, gap_after_em: float}> */
    private static function paragraphBlocks(string $body): array
    {
        $segments = preg_split('/(\n(?:[\t ]*\n)+)/', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        $blocks = [];
        foreach ($segments ?: [] as $segment) {
            if (preg_match('/^\n(?:[\t ]*\n)+$/', $segment) === 1) {
                if ($blocks !== []) {
                    $last = array_key_last($blocks);
                    $blocks[$last]['gap_after_em'] = 0.5 * (substr_count($segment, "\n") - 1);
                }

                continue;
            }
            if ($segment !== '') {
                $blocks[] = ['text' => $segment, 'gap_after_em' => 0.0];
            }
        }

        return $blocks;
    }

    /** @return list<array{text: string, gap_after_em: float}> */
    private static function legacyGroupedBlocks(string $body, int $groupSize, float $gap): array
    {
        $blocks = array_map(
            fn (array $lines): array => ['text' => implode("\n", $lines), 'gap_after_em' => 0.0],
            array_chunk(explode("\n", $body), $groupSize),
        );
        for ($index = 0; $index < count($blocks) - 1; $index++) {
            $blocks[$index]['gap_after_em'] = $gap;
        }

        return $blocks;
    }
}
