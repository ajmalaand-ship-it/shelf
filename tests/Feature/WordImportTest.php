<?php

namespace Tests\Feature;

use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Collections\RelationManagers\ContentRelationManager;
use App\Models\AppSetting;
use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use App\Models\WordImport;
use App\Support\WordImport\DocxReader;
use App\Support\WordImport\ImportWordDocument;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class WordImportTest extends TestCase
{
    use RefreshDatabase;

    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Storage::fake('sources');
    }

    private function paragraph(string $text, ?string $style = null): string
    {
        return '<w:p>'.($style ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>' : '').
            '<w:r><w:t xml:space="preserve">'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
    }

    private function docx(string $body, array $parts = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'synthetic-word-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<w:document xmlns:w="'.self::W.'"><w:body>'.$body.'</w:body></w:document>');
        foreach ($parts as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $path;
    }

    private function book(string $type = 'poetry'): Collection
    {
        return Collection::create(['title' => 'Synthetic '.$type, 'book_type' => $type]);
    }

    private function preview(string $path): array
    {
        return ['id' => (string) Str::uuid(), 'path' => $path, 'filename' => 'ازموینه.docx'] + app(DocxReader::class)->read($path);
    }

    private function manager(Collection $book)
    {
        return Livewire::test(ContentRelationManager::class, ['ownerRecord' => $book, 'pageClass' => EditCollection::class]);
    }

    public function test_exact_unicode_paragraphs_manual_breaks_and_all_splitting_rules(): void
    {
        $pashto = "  پښتو ــ ی  ي ک ك\u{200f}  ";
        $farsi = "فارسی می\u{200c}روم\u{00a0}خانه";
        $english = "English e\u{0301} & <unchanged>  ";
        $body = $this->paragraph('').$this->paragraph($pashto).
            $this->paragraph('  Original title  ', 'Heading1').
            $this->paragraph($farsi).$this->paragraph('').$this->paragraph($english).
            '<w:p><w:r><w:t>Line one</w:t><w:br/><w:t>***</w:t><w:br/><w:t>Line two</w:t><w:tab/><w:t>tab</w:t></w:r></w:p>'.
            $this->paragraph('Heading two stays', 'Heading2').$this->paragraph(' *** ').
            $this->paragraph('Next', 'LocalizedHeading').$this->paragraph('Final text').$this->paragraph('');
        $path = $this->docx($body, ['word/styles.xml' => '<w:styles xmlns:w="'.self::W.'"><w:style w:type="paragraph" w:styleId="LocalizedHeading"><w:name w:val="heading 1"/></w:style></w:styles>']);
        $before = file_get_contents($path);
        $result = app(DocxReader::class)->read($path);
        $this->assertSame([null, '  Original title  ', null, 'Next'], array_column($result['items'], 'title'));
        $this->assertSame([$pashto, $farsi."\n\n".$english."\nLine one", "Line two\ttab\nHeading two stays\n *** ", 'Final text'], array_column($result['items'], 'body'));
        $this->assertSame($farsi."\n", $result['items'][1]['first_lines']);
        $this->assertSame(2, $result['items'][3]['word_count']);
        $this->assertSame([], $result['warnings']);
        $this->assertSame($before, file_get_contents($path));
    }

    public function test_warnings_exclude_images_tables_notes_comments_and_tracked_text_and_keep_empty_items(): void
    {
        $path = $this->docx($this->paragraph('Empty', 'Heading1').$this->paragraph('***').
            $this->paragraph('Visible').'<w:p><w:r><w:drawing><w:t>Image text</w:t></w:drawing><w:footnoteReference w:id="1"/><w:commentReference w:id="1"/></w:r></w:p>'.
            '<w:tbl><w:tr><w:tc>'.$this->paragraph('Table text').'</w:tc></w:tr></w:tbl>'.
            '<w:ins>'.$this->paragraph('Inserted text').'</w:ins><w:del>'.$this->paragraph('Deleted text').'</w:del>',
            ['word/media/image.png' => 'synthetic image', 'word/footnotes.xml' => 'synthetic note', 'word/comments.xml' => 'synthetic comment']);
        $result = app(DocxReader::class)->read($path);
        $this->assertSame(['', 'Visible'], array_column($result['items'], 'body'));
        foreach (['Images', 'Tables', 'Footnotes', 'Comments', 'Tracked changes', 'Item 1 is empty'] as $warning) {
            $this->assertStringContainsString($warning, implode(' ', $result['warnings']));
        }
    }

    public function test_preview_cancel_and_confirm_for_poetry_and_prose_preserve_source_and_append_only(): void
    {
        foreach (['poetry' => 'Poem', 'prose' => 'Chapter'] as $type => $label) {
            $book = $this->book($type);
            $existing = $book->poems()->create(['body' => 'Old text', 'excerpt' => '', 'sort_order' => 9]);
            $binned = $book->poems()->create(['body' => 'Binned text', 'excerpt' => '']);
            $binned->delete();
            $other = $this->book();
            $version = AppSetting::where('key', 'content_version')->value('value');
            $path = $this->docx($this->paragraph('پښتو مخکینی متن').$this->paragraph('عنوان', 'Heading1').$this->paragraph("  فارسی می\u{200c}روم  ").$this->paragraph('***').$this->paragraph('English text'));
            $bytes = file_get_contents($path);
            $originalCount = WordImport::count();
            $component = $this->manager($book)->callTableAction('importWord', data: [
                'document' => UploadedFile::fake()->createWithContent('source.docx', file_get_contents($path)),
            ])->assertHasNoErrors()->assertActionMounted('confirmWordImport');
            $this->assertNotNull($component->get('wordPreview'));
            $this->assertNotNull($component->instance()->getMountedAction(), json_encode($component->get('mountedActions')));
            $modal = $component->instance()->getMountedAction()->getModalContent()->render();
            $this->assertStringContainsString('English text', $modal);
            $this->assertStringContainsString($label, $modal);
            $this->assertStringContainsString('First 2 lines', $modal);
            $this->assertStringContainsString('words in text', $modal);
            $component->call('$refresh');
            $this->assertNotNull($component->instance()->getMountedAction(), json_encode($component->get('mountedActions')));
            $this->assertSame(1, $book->poems()->count());
            $this->assertSame($originalCount, WordImport::count());
            $this->assertSame($version, AppSetting::where('key', 'content_version')->value('value'));
            $this->assertSame([], Storage::disk('sources')->allFiles('imports/'.$book->id));
            $staged = $component->get('wordPreview')['path'];
            $component->call('unmountAction');
            $this->assertFileDoesNotExist($staged);
            $this->assertNull($component->get('wordPreview'));
            $this->assertSame(1, $book->poems()->count());
            $this->assertSame($originalCount, WordImport::count());
            $component = $this->manager($book)->callTableAction('importWord', data: [
                'document' => UploadedFile::fake()->createWithContent('source.docx', file_get_contents($path)),
            ])->assertHasNoErrors();
            $preview = $component->get('wordPreview');
            $component->callMountedAction()->assertHasNoErrors();
            $audit = WordImport::findOrFail($preview['id']);
            $this->assertSame(3, $audit->item_count);
            $this->assertSame(auth()->id(), $audit->imported_by);
            $this->assertSame('source.docx', $audit->original_filename);
            $this->assertSame(hash('sha256', $bytes), $audit->sha256);
            $this->assertNotNull($audit->imported_at);
            $this->assertSame($bytes, Storage::disk('sources')->get($audit->source_path));
            $this->assertSame('private', Storage::disk('sources')->getVisibility($audit->source_path));
            $this->assertSame([9, 11, 12, 13], $book->poems()->pluck('sort_order')->all());
            $this->assertSame('Old text', $existing->fresh()->body);
            $this->assertSoftDeleted($binned);
            $this->assertSame(0, $other->poems()->count());
            $imported = Poem::whereIn('id', $audit->item_ids)->orderBy('sort_order')->get();
            $this->assertSame(['پښتو مخکینی متن', "  فارسی می\u{200c}روم  ", 'English text'], $imported->pluck('body')->all());
            foreach ($imported as $item) {
                $this->assertFalse($item->is_active);
                $this->assertFalse($item->is_free_sample);
                $this->assertSame('', $item->excerpt);
            }
            // Double submission of the same confirmed preview is idempotent.
            app(ImportWordDocument::class)->import($book->id, auth()->user(), $preview);
            $this->assertSame(4, $book->poems()->count());
        }
    }

    public function test_changed_file_and_invalid_documents_are_rejected_without_writes(): void
    {
        $book = $this->book();
        $path = $this->docx($this->paragraph('Original'));
        $preview = $this->preview($path);
        $changed = $this->docx($this->paragraph('Changed'));
        copy($changed, $path);
        try {
            app(ImportWordDocument::class)->import($book->id, auth()->user(), $preview);
            $this->fail('Changed source accepted');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('changed after preview', $error->getMessage());
        }
        $this->assertSame(0, $book->poems()->count());
        $this->assertSame(0, WordImport::count());
        $this->assertSame([], Storage::disk('sources')->allFiles());
        foreach (['not a zip', '<!DOCTYPE x [<!ENTITY source SYSTEM "file:///etc/passwd">]><w:document/>', '<broken>'] as $invalid) {
            $path = $this->docx('');
            if ($invalid === 'not a zip') {
                file_put_contents($path, $invalid);
            } else {
                $zip = new ZipArchive;
                $zip->open($path);
                $zip->addFromString('word/document.xml', $invalid);
                $zip->close();
            }
            try {
                app(DocxReader::class)->read($path);
                $this->fail('Invalid source accepted');
            } catch (ValidationException) {
                $this->assertSame(0, WordImport::count());
            }
        }
    }

    public function test_empty_document_and_upload_size_and_extension_validation(): void
    {
        $book = $this->book();
        $this->manager($book)->callTableAction('importWord', data: [
            'document' => UploadedFile::fake()->create('large.docx', 20481, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertHasActionErrors(['document']);
        $this->manager($book)->callTableAction('importWord', data: [
            'document' => UploadedFile::fake()->createWithContent('wrong.zip', file_get_contents($this->docx($this->paragraph('Text')))),
        ])->assertHasActionErrors(['document']);
        $empty = $this->preview($this->docx(''));
        $this->assertSame([], $empty['items']);
        $this->expectException(ValidationException::class);
        app(ImportWordDocument::class)->import($book->id, auth()->user(), $empty);
    }

    public function test_failed_import_rolls_back_all_rows_and_can_retry_without_replacing_original(): void
    {
        $book = $this->book();
        $existing = $book->poems()->create(['body' => 'Existing', 'excerpt' => '']);
        $preview = $this->preview($this->docx($this->paragraph('First').$this->paragraph('***').$this->paragraph('Second')));
        $version = AppSetting::where('key', 'content_version')->value('value');
        $dispatcher = Poem::getEventDispatcher();
        Poem::setEventDispatcher(clone $dispatcher);
        Poem::creating(function (Poem $poem): void {
            if ($poem->body === 'Second') {
                throw new \RuntimeException('Synthetic database-write failure');
            }
        });
        try {
            app(ImportWordDocument::class)->import($book->id, auth()->user(), $preview);
            $this->fail('Expected the simulated write failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic database-write failure', $error->getMessage());
        } finally {
            Poem::setEventDispatcher($dispatcher);
        }
        $this->assertSame([$existing->id], $book->poems()->pluck('id')->all());
        $this->assertSame(0, WordImport::count());
        $this->assertSame($version, AppSetting::where('key', 'content_version')->value('value'));
        $source = 'imports/'.$book->id.'/'.$preview['id'].'.docx';
        Storage::disk('sources')->assertMissing($source);
        $this->assertSame([], Storage::disk('sources')->allFiles());
        $audit = app(ImportWordDocument::class)->import($book->id, auth()->user(), $preview);
        $this->assertSame(2, $audit->item_count);
        $this->assertSame(3, $book->poems()->count());
        $this->assertSame([$source], Storage::disk('sources')->allFiles());
        $this->get('/storage/source/'.$source)->assertForbidden();
    }

    public function test_pashto_omissions_are_listed_and_require_owner_acknowledgement(): void
    {
        $book = $this->book();
        $text = '  دا د پښتو اصلي متن دی  ';
        $body = $this->paragraph($text).
            '<w:tbl><w:tr><w:tc>'.$this->paragraph('د جدول متن').'</w:tc></w:tr></w:tbl>'.
            '<w:p><w:r><w:footnoteReference w:id="1"/><w:endnoteReference w:id="1"/><w:commentReference w:id="1"/>'.
            '<w:drawing><w:txbxContent>'.$this->paragraph('د متن بکس').'</w:txbxContent></w:drawing></w:r></w:p>'.
            '<w:ins>'.$this->paragraph('بدلون').'</w:ins>';
        $path = $this->docx($body, [
            'word/media/image.png' => 'synthetic image',
            'word/footnotes.xml' => '<notes>پښتو لمنلیک</notes>',
            'word/endnotes.xml' => '<notes>پایلیک</notes>',
            'word/comments.xml' => '<comments>تبصره</comments>',
        ]);
        $preview = $this->preview($path);
        $this->assertSame($text, $preview['items'][0]['body']);
        foreach (['Images', 'Tables', 'Footnotes', 'Endnotes', 'Comments', 'Tracked changes', 'Text boxes'] as $feature) {
            $this->assertContains($feature.' found and NOT imported.', $preview['omissions']);
        }
        // Re-reading the file enforces consent even if a caller omits preview warnings.
        $forged = $preview;
        $forged['omissions'] = [];
        try {
            app(ImportWordDocument::class)->import($book->id, auth()->user(), $forged);
            $this->fail('Unacknowledged omitted content accepted');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('acknowledge_omissions', $error->errors());
        }
        $this->assertSame(0, WordImport::count());
        $this->assertSame([], Storage::disk('sources')->allFiles());
        $component = $this->manager($book)->callTableAction('importWord', data: [
            'document' => UploadedFile::fake()->createWithContent('پښتو.docx', file_get_contents($path)),
        ])->assertHasNoErrors()->assertActionMounted('confirmWordImport');
        $this->assertTrue($component->instance()->getMountedAction()->getModalSubmitAction()->isDisabled());
        $modal = $component->instance()->getMountedAction()->getModalContent()->render();
        foreach ($preview['omissions'] as $omission) {
            $this->assertStringContainsString($omission, $modal);
        }
        $component->callMountedAction()->assertHasActionErrors(['acknowledge_omissions']);
        $this->assertSame(0, $book->poems()->count());
        $this->assertSame([], Storage::disk('sources')->allFiles());
        $component->fillForm(['acknowledge_omissions' => true]);
        $this->assertFalse($component->instance()->getMountedAction()->getModalSubmitAction()->isDisabled());
        $component->callMountedAction()->assertHasNoErrors();
        $this->assertSame($text, $book->poems()->sole()->body);
        $this->assertSame(1, WordImport::count());
    }

    public function test_partial_file_copy_failure_leaves_no_orphan_or_rows(): void
    {
        $book = $this->book();
        $preview = $this->preview($this->docx($this->paragraph('Original source')));
        $importer = new class extends ImportWordDocument
        {
            protected function copySource($input, $output): void
            {
                fwrite($output, 'partial file');
                throw new \RuntimeException('Synthetic disk failure');
            }
        };
        try {
            $importer->import($book->id, auth()->user(), $preview);
            $this->fail('Expected disk failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic disk failure', $error->getMessage());
        }
        $this->assertSame(0, $book->poems()->count());
        $this->assertSame(0, WordImport::count());
        $this->assertSame([], Storage::disk('sources')->allFiles());
        $this->assertSame($preview['sha256'], hash_file('sha256', $preview['path']));
    }

    public function test_audit_write_failure_preserves_existing_items_and_originals_only(): void
    {
        $book = $this->book();
        $existing = $book->poems()->create(['body' => 'Existing content', 'excerpt' => '']);
        Storage::disk('sources')->put('imports/'.$book->id.'/committed.docx', 'Existing original');
        $preview = $this->preview($this->docx($this->paragraph('New source')));
        $dispatcher = WordImport::getEventDispatcher();
        WordImport::setEventDispatcher(clone $dispatcher);
        WordImport::creating(fn () => throw new \RuntimeException('Synthetic audit failure'));
        try {
            app(ImportWordDocument::class)->import($book->id, auth()->user(), $preview);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Synthetic audit failure', $error->getMessage());
        } finally {
            WordImport::setEventDispatcher($dispatcher);
        }
        $this->assertSame([$existing->id], $book->poems()->pluck('id')->all());
        $this->assertSame(0, WordImport::count());
        $this->assertSame(['imports/'.$book->id.'/committed.docx'], Storage::disk('sources')->allFiles());
        $this->assertSame('Existing original', Storage::disk('sources')->get('imports/'.$book->id.'/committed.docx'));
    }

    public function test_preview_cannot_be_forged_and_invalid_xml_is_a_visible_upload_error(): void
    {
        $book = $this->book();
        $this->manager($book)->callTableAction('importWord', data: [
            'document' => UploadedFile::fake()->createWithContent('broken.docx', 'invalid zip'),
        ])->assertHasActionErrors(['document']);
        $this->assertSame(0, $book->poems()->count());
        $this->assertSame(0, WordImport::count());
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $this->manager($book)->set('wordPreview', ['path' => '/forged-source.docx']);
    }

    public function test_migration_rolls_back_without_changing_content_and_home_uses_shelf(): void
    {
        $book = $this->book();
        $item = $book->poems()->create(['body' => 'Unchanged', 'excerpt' => '']);
        $migration = require database_path('migrations/2026_09_28_160000_create_word_imports_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('word_imports'));
        $this->manager($book)->assertTableActionDisabled('importWord');
        $this->assertSame('Unchanged', $item->fresh()->body);
        $migration->up();
        $this->assertTrue(Schema::hasTable('word_imports'));
        $this->get('/')->assertOk()->assertJsonPath('service', 'Shelf API');
    }
}
