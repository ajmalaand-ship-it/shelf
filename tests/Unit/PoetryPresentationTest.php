<?php

namespace Tests\Unit;

use App\Models\Poem;
use App\Support\PoetryPresentation;
use PHPUnit\Framework\TestCase;

class PoetryPresentationTest extends TestCase
{
    public function test_plain_unicode_body_is_not_modified_by_presentation(): void
    {
        $body = "لومړۍ — «کرښه»\nدويمه\n\nدرېيمه\nڅلورمه";
        $blocks = PoetryPresentation::blocks($body, Poem::LAYOUT_SOURCE);

        $this->assertSame($body, $blocks[0]['text']."\n\n".$blocks[1]['text']);
    }

    public function test_paragraph_boundaries_are_explicit_half_em_gaps_not_empty_rows(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n۳\n۴", Poem::LAYOUT_SOURCE);

        $this->assertSame([
            ['text' => "۱\n۲", 'gap_after_em' => 0.5],
            ['text' => "۳\n۴", 'gap_after_em' => 0.0],
        ], $blocks);
        $this->assertNotContains('', array_column($blocks, 'text'));
    }

    public function test_repeated_blank_boundaries_increase_spacing_conservatively(): void
    {
        $blocks = PoetryPresentation::blocks("۱\n۲\n\n\n۳\n۴", Poem::LAYOUT_SOURCE);
        $this->assertSame(1.0, $blocks[0]['gap_after_em']);
    }

    public function test_legacy_modes_group_only_when_no_paragraph_boundary_exists(): void
    {
        $this->assertSame([0.5, 0.0], array_column(
            PoetryPresentation::blocks("۱\n۲\n۳\n۴", Poem::LAYOUT_COUPLET),
            'gap_after_em',
        ));
        $this->assertSame([0.5, 0.0], array_column(
            PoetryPresentation::blocks("۱\n۲\n\n۳\n۴", Poem::LAYOUT_FOUR_LINES),
            'gap_after_em',
        ));
        $this->assertSame([1.1, 0.0], array_column(
            PoetryPresentation::blocks("۱\n۲\n۳\n۴\n۵", Poem::LAYOUT_FOUR_LINES),
            'gap_after_em',
        ));
    }
}
