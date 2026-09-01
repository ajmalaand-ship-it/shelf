<?php

namespace App\Support;

use App\Models\Poem;

final class PoetryPresentation
{
    /**
     * @return list<array{text: string, gap_after_lines: float}>
     */
    public static function blocks(string $body, string $layoutMode): array
    {
        if ($layoutMode === Poem::LAYOUT_SOURCE) {
            return [['text' => $body, 'gap_after_lines' => 0.0]];
        }

        if ($layoutMode === Poem::LAYOUT_COUPLET) {
            return self::coupletBlocks($body);
        }

        return self::groupedBlocks($body, 4);
    }

    /**
     * A single blank line between complete bayts is the authored separator for
     * the same half-line presentation gap. Further blank lines remain visible.
     *
     * @return list<array{text: string, gap_after_lines: float}>
     */
    private static function coupletBlocks(string $body): array
    {
        $segments = preg_split('/(\n(?:[\t ]*\n)+)/', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        $blocks = [];

        foreach ($segments ?: [] as $segment) {
            if (preg_match('/^\n(?:[\t ]*\n)+$/', $segment) === 1) {
                $blankCount = substr_count($segment, "\n") - 1;
                if ($blocks === []) {
                    $blocks[] = ['text' => '', 'gap_after_lines' => (float) $blankCount];

                    continue;
                }

                $last = array_key_last($blocks);
                $isCompleteBayt = substr_count($blocks[$last]['text'], "\n") === 1;
                $blocks[$last]['gap_after_lines'] = $isCompleteBayt
                    ? 0.5 + max(0, $blankCount - 1)
                    : (float) $blankCount;

                continue;
            }

            $lines = explode("\n", $segment);
            foreach (array_chunk($lines, 2) as $linesInBayt) {
                if ($blocks !== [] && $blocks[array_key_last($blocks)]['gap_after_lines'] === 0.0) {
                    $blocks[array_key_last($blocks)]['gap_after_lines'] = 0.5;
                }

                $blocks[] = ['text' => implode("\n", $linesInBayt), 'gap_after_lines' => 0.0];
            }
        }

        return $blocks;
    }

    /**
     * @return list<array{text: string, gap_after_lines: float}>
     */
    private static function groupedBlocks(string $body, int $groupSize): array
    {
        $groups = [];
        $current = [];

        foreach (explode("\n", $body) as $line) {
            if ($line === '') {
                if ($current !== []) {
                    $groups[] = implode("\n", $current);
                    $current = [];
                }
                $groups[] = '';

                continue;
            }

            $current[] = $line;
            if (count($current) === $groupSize) {
                $groups[] = implode("\n", $current);
                $current = [];
            }
        }

        if ($current !== []) {
            $groups[] = implode("\n", $current);
        }

        return array_map(
            fn (string $group, int $index): array => [
                'text' => $group,
                'gap_after_lines' => $index < count($groups) - 1 ? 0.5 : 0.0,
            ],
            $groups,
            array_keys($groups),
        );
    }
}
