<?php

namespace App\Support;

use App\Models\Poem;

final class PoetryPresentation
{
    public const GAP_NONE = 'NONE';

    public const GAP_HALF = 'HALF';

    public const GAP_FULL = 'FULL';

    public const GAPS = [self::GAP_NONE, self::GAP_HALF, self::GAP_FULL];

    /** @return list<array{text: string, gap_after_em: float}> */
    public static function blocks(string $body, string $layoutMode, ?array $manualSpacing = null): array
    {
        if ($layoutMode === Poem::LAYOUT_SOURCE) {
            return [['text' => $body, 'gap_after_em' => 0.0]];
        }
        if (self::isValidForBody($manualSpacing, $body)) {
            return self::manualBlocks($body, $manualSpacing);
        }
        if ($layoutMode === Poem::LAYOUT_COUPLET) {
            return self::coupletBlocks($body);
        }

        return self::groupedBlocks($body, 4);
    }

    /** @return list<array{after_line: int, line: string, gap: string}> */
    public static function controlRows(string $body, ?array $controls = null): array
    {
        $existing = [];
        foreach ($controls ?? [] as $control) {
            $afterLine = filter_var($control['after_line'] ?? null, FILTER_VALIDATE_INT);
            $gap = $control['gap'] ?? self::GAP_NONE;
            if ($afterLine !== false && in_array($gap, self::GAPS, true)) {
                $existing[$afterLine] = $gap;
            }
        }

        $rows = [];
        foreach (array_slice(self::literaryLines($body), 0, -1) as $index => $line) {
            $afterLine = $index + 1;
            $rows[] = [
                'after_line' => $afterLine,
                'line' => $line,
                'gap' => $existing[$afterLine] ?? self::GAP_NONE,
            ];
        }

        return $rows;
    }

    /** @return list<array{after_line: int, line: string, gap: string}> */
    public static function controlRowsFromStored(string $body, ?array $spacing): array
    {
        $controls = [];
        if (is_array($spacing['gaps'] ?? null)) {
            foreach ($spacing['gaps'] as $gap) {
                if (is_array($gap)) {
                    $controls[] = $gap;
                }
            }
        }

        return self::controlRows($body, $controls);
    }

    /** @return array{version: int, line_count: int, gaps: list<array{after_line: int, gap: string}>}|null */
    public static function fromControls(string $body, bool $enabled, ?array $controls): ?array
    {
        if (! $enabled) {
            return null;
        }

        $lineCount = count(self::literaryLines($body));
        $gaps = [];
        foreach ($controls ?? [] as $control) {
            $afterLine = filter_var($control['after_line'] ?? null, FILTER_VALIDATE_INT);
            $gap = $control['gap'] ?? self::GAP_NONE;
            if ($afterLine === false || $afterLine < 1 || $afterLine >= $lineCount || ! in_array($gap, self::GAPS, true)) {
                continue;
            }
            if ($gap !== self::GAP_NONE) {
                $gaps[$afterLine] = ['after_line' => $afterLine, 'gap' => $gap];
            }
        }
        ksort($gaps, SORT_NUMERIC);

        return ['version' => 1, 'line_count' => $lineCount, 'gaps' => array_values($gaps)];
    }

    public static function isValidForBody(?array $spacing, string $body): bool
    {
        if (($spacing['version'] ?? null) !== 1
            || ($spacing['line_count'] ?? null) !== count(self::literaryLines($body))
            || ! is_array($spacing['gaps'] ?? null)) {
            return false;
        }
        foreach ($spacing['gaps'] as $gap) {
            if (! is_array($gap)
                || ! is_int($gap['after_line'] ?? null)
                || $gap['after_line'] < 1
                || $gap['after_line'] >= $spacing['line_count']
                || ! in_array($gap['gap'] ?? null, [self::GAP_HALF, self::GAP_FULL], true)) {
                return false;
            }
        }

        return true;
    }

    /** @return list<array{text: string, gap_after_em: float}> */
    private static function manualBlocks(string $body, array $spacing): array
    {
        $blocks = [];
        $current = [];
        $gaps = [];
        foreach ($spacing['gaps'] as $gap) {
            $gaps[$gap['after_line']] = $gap['gap'];
        }
        foreach (self::literaryLines($body) as $index => $line) {
            $current[] = $line;
            $gap = $gaps[$index + 1] ?? self::GAP_NONE;
            if ($gap === self::GAP_NONE && $index < $spacing['line_count'] - 1) {
                continue;
            }
            $blocks[] = [
                'text' => implode("\n", $current),
                'gap_after_em' => match ($gap) {
                    self::GAP_HALF => 0.5,
                    self::GAP_FULL => 1.0,
                    default => 0.0,
                },
            ];
            $current = [];
        }

        return $blocks;
    }

    /** @return list<array{text: string, gap_after_em: float}> */
    private static function coupletBlocks(string $body): array
    {
        $segments = preg_split('/(\n(?:[\t ]*\n)+)/', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        $blocks = [];
        foreach ($segments ?: [] as $segment) {
            if (preg_match('/^\n(?:[\t ]*\n)+$/', $segment) === 1) {
                $blankCount = substr_count($segment, "\n") - 1;
                if ($blocks === []) {
                    $blocks[] = ['text' => '', 'gap_after_em' => $blankCount * 2.2];

                    continue;
                }
                $last = array_key_last($blocks);
                $isCompleteBayt = substr_count($blocks[$last]['text'], "\n") === 1;
                $blocks[$last]['gap_after_em'] = $isCompleteBayt
                    ? 0.5 + max(0, $blankCount - 1) * 2.2
                    : $blankCount * 2.2;

                continue;
            }
            $lines = explode("\n", $segment);
            foreach (array_chunk($lines, 2) as $linesInBayt) {
                if ($blocks !== [] && $blocks[array_key_last($blocks)]['gap_after_em'] === 0.0) {
                    $blocks[array_key_last($blocks)]['gap_after_em'] = 0.5;
                }
                $blocks[] = ['text' => implode("\n", $linesInBayt), 'gap_after_em' => 0.0];
            }
        }

        return $blocks;
    }

    /** @return list<array{text: string, gap_after_em: float}> */
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
                'gap_after_em' => $index < count($groups) - 1 ? 1.1 : 0.0,
            ],
            $groups,
            array_keys($groups),
        );
    }

    /** @return list<string> */
    private static function literaryLines(string $body): array
    {
        return array_values(array_filter(explode("\n", $body), fn (string $line): bool => trim($line) !== ''));
    }
}
