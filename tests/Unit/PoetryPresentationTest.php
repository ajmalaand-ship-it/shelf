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
            [['text' => $body, 'gap_after_lines' => 0.0]],
            PoetryPresentation::blocks($body, Poem::LAYOUT_SOURCE),
        );
    }

    public function test_couplet_uses_one_half_line_for_plain_or_single_blank_separated_bayts(): void
    {
        $expected = [
            ['text' => "۱\n۲", 'gap_after_lines' => 0.5],
            ['text' => "۳\n۴", 'gap_after_lines' => 0.0],
        ];

        $this->assertSame($expected, PoetryPresentation::blocks("۱\n۲\n۳\n۴", Poem::LAYOUT_COUPLET));
        $this->assertSame($expected, PoetryPresentation::blocks("۱\n۲\n\n۳\n۴", Poem::LAYOUT_COUPLET));
    }

    public function test_couplet_preserves_larger_breaks_as_additional_full_line_space(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n\n۳\n۴", Poem::LAYOUT_COUPLET);

        $this->assertSame(1.5, $blocks[0]['gap_after_lines']);
        $this->assertSame("۱\n۲\n\n\n۳\n۴", "{$blocks[0]['text']}\n\n\n{$blocks[1]['text']}");
    }

    public function test_four_line_grouping_retains_the_existing_blank_group_behavior(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n۳\n۴\n۵\n۶", Poem::LAYOUT_FOUR_LINES);

        $this->assertSame(["۱\n۲", '', "۳\n۴\n۵\n۶"], array_column($blocks, 'text'));
        $this->assertSame([0.5, 0.5, 0.0], array_column($blocks, 'gap_after_lines'));
    }
}
