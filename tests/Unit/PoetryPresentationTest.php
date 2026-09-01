<?php

namespace Tests\Unit;

use App\Models\Poem;
use App\Support\PoetryEditorDocument;
use App\Support\PoetryPresentation;
use PHPUnit\Framework\TestCase;

class PoetryPresentationTest extends TestCase
{
    public function test_editor_round_trips_plain_unicode_lines_and_paragraphs(): void
    {
        $body = "لومړۍ — «کرښه»\nدويمه\n\nدرېيمه\nڅلورمه";
        $html = PoetryEditorDocument::toEditorHtml($body);

        $this->assertSame('<p>لومړۍ — «کرښه»<br>دويمه</p><p>درېيمه<br>څلورمه</p>', $html);
        $this->assertSame($body, PoetryEditorDocument::toPlainText($html));
        $this->assertStringNotContainsString('<', PoetryEditorDocument::toPlainText($html));
    }

    public function test_enter_and_shift_enter_have_deterministic_plain_text_meanings(): void
    {
        $this->assertSame("لومړۍ\nدويمه", PoetryEditorDocument::toPlainText('<p>لومړۍ<br>دويمه</p>'));
        $this->assertSame("لومړۍ\n\nدويمه", PoetryEditorDocument::toPlainText('<p>لومړۍ</p><p>دويمه</p>'));
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
