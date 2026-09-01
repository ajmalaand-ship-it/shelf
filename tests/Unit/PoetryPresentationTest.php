<?php

namespace Tests\Unit;

use App\Models\Poem;
use App\Support\PoetryPresentation;
use PHPUnit\Framework\TestCase;

class PoetryPresentationTest extends TestCase
{
    public function test_source_preserves_the_complete_source_as_one_unchanged_block(): void
    {
        $body = "لومړۍ\n\nدويمه\n\n\nدرېيمه";

        $this->assertSame(
            [['text' => $body, 'gap_after_em' => 0.0]],
            PoetryPresentation::blocks($body, Poem::LAYOUT_SOURCE),
        );
    }

    public function test_couplet_uses_one_half_line_for_plain_or_single_blank_separated_bayts(): void
    {
        $expected = [
            ['text' => "۱\n۲", 'gap_after_em' => 0.5],
            ['text' => "۳\n۴", 'gap_after_em' => 0.0],
        ];

        $this->assertSame($expected, PoetryPresentation::blocks("۱\n۲\n۳\n۴", Poem::LAYOUT_COUPLET));
        $this->assertSame($expected, PoetryPresentation::blocks("۱\n۲\n\n۳\n۴", Poem::LAYOUT_COUPLET));
    }

    public function test_couplet_preserves_larger_breaks_as_additional_full_line_space(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n\n۳\n۴", Poem::LAYOUT_COUPLET);

        $this->assertSame(2.7, $blocks[0]['gap_after_em']);
        $this->assertSame("۱\n۲\n\n\n۳\n۴", "{$blocks[0]['text']}\n\n\n{$blocks[1]['text']}");
    }

    public function test_four_line_grouping_retains_the_existing_blank_group_behavior(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n۳\n۴\n۵\n۶", Poem::LAYOUT_FOUR_LINES);

        $this->assertSame(["۱\n۲", '', "۳\n۴\n۵\n۶"], array_column($blocks, 'text'));
        $this->assertSame([1.1, 1.1, 0.0], array_column($blocks, 'gap_after_em'));
    }

    public function test_manual_spacing_represents_none_half_and_full_without_changing_body(): void
    {
        $body = "۱\n۲\n\n۳\n۴";
        $spacing = PoetryPresentation::fromControls($body, true, [
            ['after_line' => 1, 'gap' => PoetryPresentation::GAP_HALF],
            ['after_line' => 2, 'gap' => PoetryPresentation::GAP_NONE],
            ['after_line' => 3, 'gap' => PoetryPresentation::GAP_FULL],
        ]);

        $this->assertSame([
            'version' => 1,
            'line_count' => 4,
            'gaps' => [
                ['after_line' => 1, 'gap' => 'HALF'],
                ['after_line' => 3, 'gap' => 'FULL'],
            ],
        ], $spacing);
        $this->assertSame([
            ['text' => '۱', 'gap_after_em' => 0.5],
            ['text' => "۲\n۳", 'gap_after_em' => 1.0],
            ['text' => '۴', 'gap_after_em' => 0.0],
        ], PoetryPresentation::blocks($body, Poem::LAYOUT_COUPLET, $spacing));
        $this->assertSame("۱\n۲\n\n۳\n۴", $body);
    }

    public function test_manual_spacing_is_ignored_safely_if_line_count_becomes_stale(): void
    {
        $spacing = ['version' => 1, 'line_count' => 4, 'gaps' => [['after_line' => 2, 'gap' => 'HALF']]];

        $this->assertFalse(PoetryPresentation::isValidForBody($spacing, "۱\n۲\n۳"));
        $this->assertSame(
            PoetryPresentation::blocks("۱\n۲\n۳", Poem::LAYOUT_COUPLET),
            PoetryPresentation::blocks("۱\n۲\n۳", Poem::LAYOUT_COUPLET, $spacing),
        );
    }
}
