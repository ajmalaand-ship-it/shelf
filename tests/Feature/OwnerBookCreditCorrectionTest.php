<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Collection;
use App\Models\Poem;
use App\Support\BookCreditCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerBookCreditCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_27_140000_correct_owner_book_credits.php');
    }

    private function catalogue(): int
    {
        $this->migration()->down();
        $ajmal = Author::create(['name' => 'اجمل اند', 'name_latin' => 'Ajmal Aand']);
        $parwin = Author::create(['name' => 'پروین پژواک']);
        foreach ([3, 4, 5, 6, 7, 8] as $id) {
            $book = Collection::factory()->create(['id' => $id, 'title' => 'Synthetic book '.$id, 'slug' => 'synthetic-'.$id, 'language' => $id < 7 ? 'ps' : null]);
            $book->credits()->create(['author_id' => $ajmal->id, 'role' => 'author', 'position' => 4]);
            $book->credits()->create(['author_id' => $parwin->id, 'role' => 'author', 'position' => 7]);
            $book->credits()->create(['author_id' => $ajmal->id, 'role' => 'translator', 'position' => 9]);
        }
        for ($i = 0; $i < 4; $i++) {
            Poem::factory()->create(['body' => 'Synthetic body', 'excerpt' => 'Synthetic', 'collection_id' => 4, 'work_type' => 'TRANSLATION',
                'original_author' => 'پروین پژواک', 'translator' => $i % 2 ? 'Ajmal Aand' : 'اجمل اند',
                'source_note' => 'Synthetic source note']);
        }
        Poem::factory()->create(['body' => 'Synthetic body', 'excerpt' => 'Synthetic', 'collection_id' => 4, 'work_type' => 'ORIGINAL', 'original_author' => null]);
        Poem::factory()->create(['body' => 'Synthetic body', 'excerpt' => 'Synthetic', 'collection_id' => 6, 'work_type' => 'TRANSLATION',
            'original_author' => 'پروین پژواک', 'translator' => 'اجمل اند']);

        return Poem::factory()->create(['body' => 'Synthetic body', 'excerpt' => 'Synthetic', 'collection_id' => 8, 'work_type' => 'TRANSLATION',
            'original_author' => 'نوم نه دی ښودل شوی', 'translator' => 'Ajmal Aand'])->id;
    }

    private function state(): array
    {
        return collect(['collections', 'poems', 'collection_author', 'authors'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()])->all();
    }

    public function test_exact_correction_and_rollback_preserve_all_other_values(): void
    {
        $unknownId = $this->catalogue();
        $before = $this->state();
        $unknown = (array) DB::table('poems')->find($unknownId);
        $this->assertFalse(BookCreditCheck::report()['valid']);
        $this->migration()->up();
        $report = BookCreditCheck::report();
        $this->assertTrue($report['valid']);
        $this->assertSame([$unknownId], array_column($report['book_8_unknown_author_unchanged'], 'id'));
        $this->assertSame($unknown, (array) DB::table('poems')->find($unknownId));
        $previousPoems = json_decode($before['poems'], true);
        foreach ($previousPoems as $poem) {
            if ($poem['collection_id'] === 4 && $poem['original_author'] === 'پروین پژواک') {
                $poem['work_type'] = 'ORIGINAL';
                $poem['original_author'] = 'اجمل اند';
                $poem['translator'] = null;
            }
            $this->assertSame($poem, (array) DB::table('poems')->find($poem['id']));
        }
        $this->migration()->down();
        $this->assertSame($before, $this->state());
        $this->migration()->up();
        $this->assertTrue(BookCreditCheck::report()['valid']);
    }

    public function test_checker_rejects_extra_missing_wrong_credits_language_and_books(): void
    {
        $this->catalogue();
        $this->migration()->up();
        $ajmal = Author::where('name', 'اجمل اند')->sole();
        foreach ([3, 4, 5, 6, 7, 8] as $id) {
            $credit = DB::table('collection_author')->insertGetId([
                'collection_id' => $id, 'author_id' => $ajmal->id, 'role' => 'editor', 'position' => 3,
            ]);
            $this->assertFalse(BookCreditCheck::report()['valid']);
            DB::table('collection_author')->where('id', $credit)->delete();
            DB::table('collections')->where('id', $id)->update(['language' => 'fa']);
            $this->assertFalse(BookCreditCheck::report()['valid']);
            DB::table('collections')->where('id', $id)->update(['language' => 'ps']);
        }
        DB::table('collection_author')->where('collection_id', 6)->where('role', 'translator')->delete();
        $this->assertFalse(BookCreditCheck::report()['valid']);
        $this->migration()->down();
        $this->migration()->up();
        DB::table('collection_author')->where('collection_id', 3)->update(['author_id' => Author::where('name', 'پروین پژواک')->sole()->id]);
        $this->assertFalse(BookCreditCheck::report()['valid']);
        $this->migration()->down();
        $this->migration()->up();
        Collection::factory()->create(['id' => 9, 'title' => 'Extra book', 'slug' => 'extra', 'language' => 'ps']);
        $this->assertFalse(BookCreditCheck::report()['valid']);
        DB::table('collections')->where('id', 9)->delete();
        DB::table('collections')->where('id', 8)->delete();
        $this->assertFalse(BookCreditCheck::report()['valid']);
    }

    public function test_unexpected_poem_count_fails_without_partial_changes(): void
    {
        $this->catalogue();
        DB::table('poems')->where('collection_id', 4)->where('original_author', 'پروین پژواک')->limit(1)->delete();
        $before = $this->state();
        try {
            $this->migration()->up();
            $this->fail('Unexpected catalogue must be refused.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('four matching poems', $error->getMessage());
        }
        $this->assertSame($before, $this->state());
    }

    public function test_missing_snapshot_refuses_rollback(): void
    {
        $this->migration()->down();
        $this->expectExceptionMessage('snapshot missing');
        $this->migration()->down();
    }
}
