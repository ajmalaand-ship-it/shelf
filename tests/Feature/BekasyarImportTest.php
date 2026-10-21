<?php

namespace Tests\Feature;

use App\Console\Commands\ImportBekasyarBook;
use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use App\Models\WordImport;
use App\Support\WordImport\BekasyarImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class BekasyarImportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private array $plan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('sources');
        $this->owner = User::factory()->state(['is_owner' => true])->create();
        $this->actingAs($this->owner);
        $book = new Collection(['title' => 'Synthetic reviewed book', 'book_type' => 'poetry', 'language' => 'ps', 'price_usd' => '3.99']);
        $book->id = 13;
        $book->save();
        for ($i = 0; $i < 6; $i++) {
            $p = $book->poems()->create(['body' => 'Binned source '.$i, 'excerpt' => '']);
            $p->delete();
        }
        $archive = tempnam(getenv('SHELF_TEST_TMP'), 'bekasyar-synthetic-');
        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::OVERWRITE);
        $docs = [];
        $seq = 0;
        foreach ([48, 47, 77, 11] as $index => $count) {
            $name = ['A.Bekasyar...1.docx', 'A.Bekasyar...2.docx', 'A.Bekasyar...3.docx', 'Nazmona.docx'][$index];
            $bytes = 'Synthetic original '.$name;
            $zip->addFromString($name, $bytes);
            $items = [];
            for ($i = 0; $i < $count; $i++) {
                ++$seq;
                $body = "\n  پښتو ی ي ک ك\u{200c}  {$seq}\nښی۱\nکيڼ۱\nښی۲\nکيڼ۲\n".($seq === 12 ? "\n(" : '');
                $items[] = ['sequence' => $seq, 'title' => $index < 3 ? null : 'Supplied title '.$i, 'body' => $body, 'body_sha256' => hash('sha256', $body)];
            }
            $docs[] = ['filename' => $name, 'id' => (string) Str::uuid(), 'sha256' => hash('sha256', $bytes), 'items' => $items, 'warnings' => ['Synthetic editorial flag: preserve (']];
        }
        $zip->close();
        $manifest = tempnam(getenv('SHELF_TEST_TMP'), 'bekasyar-plan-');
        file_put_contents($manifest, 'Synthetic reviewed manifest');
        $this->plan = ['documents' => $docs, '_archive_path' => $archive, '_manifest_path' => $manifest,
            '_archive_sha256' => hash_file('sha256', $archive), '_manifest_sha256' => hash_file('sha256', $manifest),
            'baseline' => ['book' => $book->fresh()->getAttributes(), 'credits' => [], 'categories' => [],
                'poems' => $book->poems()->withTrashed()->orderBy('id')->get()->map->getAttributes()->all()]];
    }

    public function test_preview_apply_replay_and_reversible_migration_keep_original_bin_and_exact_text(): void
    {
        $s = app(BekasyarImport::class);
        $this->assertFalse($s->inspect($this->plan));
        $this->assertSame(0, Poem::count());
        $this->assertSame([], Storage::disk('sources')->allFiles());
        app()->instance('shelf.bekasyar.reviewed-request', ['manifest' => $this->plan, 'actor' => $this->owner]);
        $migration = require base_path('scripts/book_import/migrations/'.BekasyarImport::MIGRATION.'.php');
        $migration->up();
        $this->assertTrue($s->inspect($this->plan));
        $this->assertSame(183, Poem::count());
        $this->assertSame(189, Poem::withTrashed()->count());
        $this->assertSame(4, WordImport::count());
        $this->assertSame(6, count(Storage::disk('sources')->allFiles()));
        $this->assertSame(172, Poem::whereNull('title')->count());
        foreach ($this->plan['documents'] as $d) {
            $audit = WordImport::findOrFail($d['id']);
            $this->assertSame($this->owner->id, $audit->imported_by);
            foreach ($audit->item_ids as $i => $id) {
                $this->assertSame($d['items'][$i]['body'], Poem::findOrFail($id)->body);
            }
        }
        $s->apply($this->plan, $this->owner);
        $this->assertSame(183, Poem::count());
        $migration->down();
        $this->assertSame(0, Poem::count());
        $this->assertSame(189, Poem::onlyTrashed()->count());
        $this->assertSame(4, WordImport::count());
        $this->assertSame($this->plan['baseline']['poems'], Poem::withTrashed()->whereIn('id', array_column($this->plan['baseline']['poems'], 'id'))->orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame('draft', Collection::findOrFail(13)->status);
        $this->assertSame('3.99', Collection::findOrFail(13)->price_usd);
    }

    public function test_later_document_failure_rolls_back_all_items_audits_and_only_attempt_files(): void
    {
        Storage::disk('sources')->put('unrelated-original.docx', 'Must remain');
        $count = 0;
        Poem::creating(function () use (&$count) { if (++$count === 60) { throw new RuntimeException('Synthetic later-document failure'); } });
        try { app(BekasyarImport::class)->apply($this->plan, $this->owner); $this->fail('Failure expected'); }
        catch (RuntimeException $e) { $this->assertSame('Synthetic later-document failure', $e->getMessage()); }
        $this->assertSame(0, Poem::count());
        $this->assertSame(6, Poem::withTrashed()->count());
        $this->assertSame(0, WordImport::count());
        $this->assertSame(['unrelated-original.docx'], Storage::disk('sources')->allFiles());
    }

    public function test_changed_book_and_wrong_manifest_are_rejected_before_writes(): void
    {
        $b = Collection::findOrFail(13);
        $b->title = 'Changed by owner';
        $b->save();
        try { app(BekasyarImport::class)->inspect($this->plan); $this->fail('Conflict expected'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('metadata changed', $e->getMessage()); }
        try { app(BekasyarImport::class)->load($this->plan['_manifest_path'], $this->plan['_archive_path'], str_repeat('0', 64)); $this->fail('Checksum rejection expected'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('checksum mismatch', $e->getMessage()); }
        $this->assertSame([], Storage::disk('sources')->allFiles());
        $this->assertSame(0, Poem::count());
    }

    public function test_nonowner_and_existing_source_destination_are_never_adopted(): void
    {
        $other = User::factory()->create();
        try { app(BekasyarImport::class)->apply($this->plan, $other); $this->fail('Owner required'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('owner actor', $e->getMessage()); }
        $dest = 'imports/13/'.$this->plan['documents'][0]['id'].'.docx';
        Storage::disk('sources')->put($dest, 'Pre-existing original');
        try { app(BekasyarImport::class)->apply($this->plan, $this->owner); $this->fail('Collision expected'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('destination already exists', $e->getMessage()); }
        $this->assertSame('Pre-existing original', Storage::disk('sources')->get($dest));
        $this->assertSame(0, WordImport::count());
        $this->assertSame(0, Poem::count());
    }

    public function test_actual_approved_manifest_rehearsal_when_private_sources_are_supplied(): void
    {
        $path = getenv('SHELF_BEKASYAR_REVIEW_MANIFEST');
        if (! $path) { $this->markTestSkipped('Private owner source is supplied only for the import rehearsal.'); }
        $archive = getenv('SHELF_BEKASYAR_REVIEW_ARCHIVE');
        $s = app(BekasyarImport::class);
        $m = $s->load($path, $archive, hash_file('sha256', $path));
        // Only the target metadata/bin fingerprint is replaced with the synthetic
        // database. The original source manifest and all 183 bodies remain exact.
        $m['baseline'] = $this->plan['baseline'];
        $s->apply($m, $this->owner);
        $this->assertTrue($s->inspect($m));
        $this->assertSame(183, Poem::count());
        $this->assertSame(172, Poem::whereNull('title')->count());
        foreach ($m['documents'] as $d) {
            $ids = WordImport::findOrFail($d['id'])->item_ids;
            foreach ($d['items'] as $i => $p) { $this->assertSame($p['body'], Poem::findOrFail($ids[$i])->body); }
        }
        $s->apply($m, $this->owner);
        $this->assertSame(183, Poem::count());
        $s->reverse($m);
        $this->assertSame(0, Poem::count());
    }

    public function test_edit_after_import_blocks_reversal_instead_of_deleting_new_owner_work(): void
    {
        $s = app(BekasyarImport::class);
        $s->apply($this->plan, $this->owner);
        $p = Poem::firstOrFail();
        $p->body .= ' Owner edit';
        $p->save();
        try { $s->reverse($this->plan); $this->fail('Edited work must be protected'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('item changed', $e->getMessage()); }
        $this->assertSame(183, Poem::count());
    }
}
