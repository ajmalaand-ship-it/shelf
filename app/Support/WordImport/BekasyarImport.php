<?php

namespace App\Support\WordImport;

use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use App\Models\WordImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

/** One reviewed add-only import, using the existing transactional Word service. */
class BekasyarImport
{
    public const ARCHIVE_SHA = '76b49b2c0ae5d21be7b5dff760925c76df67500654a6881e243709e95de2678f';
    public const MIGRATION = '2026_10_21_000001_import_reviewed_bekasyar_poems';

    public function load(string $manifestPath, string $archivePath, string $approvedHash): array
    {
        $this->check(is_file($manifestPath) && hash_equals($approvedHash, hash_file('sha256', $manifestPath)), 'Approved manifest checksum mismatch.');
        $m = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $this->check(($m['schema'] ?? null) === 1 && ($m['book_id'] ?? null) === 13 && ($m['total_poems'] ?? null) === 183, 'Wrong import plan.');
        $this->check(($m['archive_sha256'] ?? '') === self::ARCHIVE_SHA && is_file($archivePath) && hash_file('sha256', $archivePath) === self::ARCHIVE_SHA, 'Original ZIP checksum mismatch.');
        $sequence = 0;
        $this->check(count($m['documents']) === 4, 'Four source documents required.');
        foreach ($m['documents'] as $i => $d) {
            $this->check($d['filename'] === ['A.Bekasyar...1.docx', 'A.Bekasyar...2.docx', 'A.Bekasyar...3.docx', 'Nazmona.docx'][$i] && count($d['items']) === [48, 47, 77, 11][$i], 'Source order/count changed.');
            foreach ($d['items'] as $p) {
                $this->check($p['sequence'] === ++$sequence && hash('sha256', $p['body']) === $p['body_sha256'], 'Poem sequence/body mismatch.');
                $this->check($i >= 3 || $p['title'] === null, 'Ghazals must remain untitled.');
            }
        }
        $m['_manifest_path'] = $manifestPath;
        $m['_manifest_sha256'] = $approvedHash;
        $m['_archive_path'] = $archivePath;
        $m['_archive_sha256'] = self::ARCHIVE_SHA;
        return $m;
    }

    public function inspect(array $m): bool
    {
        return DB::transaction(function () use ($m) {
            $book = Collection::withTrashed()->whereKey(13)->lockForUpdate()->firstOrFail();
            $audits = WordImport::whereIn('id', array_column($m['documents'], 'id'))->get()->keyBy('id');
            $this->check($audits->isEmpty() || $audits->count() === 4, 'Partial/conflicting audit exists.');
            $attributes = $book->getAttributes();
            $expected = $m['baseline']['book'];
            if ($audits->isNotEmpty()) {
                foreach (['updated_at', 'updated_by'] as $key) { unset($attributes[$key], $expected[$key]); }
            }
            $this->check($book->status === 'draft' && ! $book->trashed() && $attributes == $expected, 'Draft metadata changed; preview again.');
            $this->check($book->credits()->orderBy('id')->get()->map->getAttributes()->all() == $m['baseline']['credits'], 'Book credits changed.');
            $this->check($book->categories()->pluck('categories.id')->all() == $m['baseline']['categories'], 'Categories changed.');
            $oldIds = array_column($m['baseline']['poems'], 'id');
            $this->check($book->poems()->withTrashed()->whereIn('id', $oldIds)->orderBy('id')->get()->map->getAttributes()->all() == $m['baseline']['poems'], 'Existing/bin content changed.');
            if ($audits->isEmpty()) {
                $this->check($book->poems()->withTrashed()->count() === count($oldIds), 'Unexpected content exists.');
                return false;
            }
            foreach (['original.zip' => $m['_archive_sha256'], 'manifest.json' => $m['_manifest_sha256']] as $name => $hash) {
                $proof = Storage::disk('sources')->path('imports/13/bekasyar-'.self::ARCHIVE_SHA.'/'.$name);
                $this->check(is_file($proof) && hash_file('sha256', $proof) === $hash, 'Archived source/manifest proof mismatch.');
            }
            $ids = [];
            foreach ($m['documents'] as $d) {
                $audit = $audits[$d['id']];
                $this->check($audit->collection_id === 13 && $audit->sha256 === $d['sha256'] && count($audit->item_ids) === count($d['items']), 'Import audit conflict.');
                $source = Storage::disk('sources')->path($audit->source_path);
                $this->check(is_file($source) && hash_file('sha256', $source) === $d['sha256'], 'Archived original mismatch.');
                foreach ($audit->item_ids as $i => $id) {
                    $poem = Poem::withTrashed()->findOrFail($id);
                    $p = $d['items'][$i];
                    $this->check($poem->collection_id === 13 && ! $poem->trashed() && $poem->title === $p['title'] && hash('sha256', $poem->body) === $p['body_sha256'] && ! $poem->is_active && $poem->sample_mode === 'none', 'An imported item changed or was removed; no duplicate import allowed.');
                    $this->check($poem->sort_order === count($oldIds) + $p['sequence'] && $poem->layout_mode === Poem::LAYOUT_SOURCE && $poem->work_type === 'ORIGINAL' && $poem->excerpt === '' && $poem->source_note === null && $poem->audio_path === null && $poem->artwork_path === null && $poem->original_author === null && $poem->translator === null && $poem->source_date_place === null, 'Imported metadata changed; refusing replay/reversal.');
                    $ids[] = $id;
                }
            }
            $this->check($book->poems()->withTrashed()->whereNotIn('id', array_merge($oldIds, $ids))->count() === 0, 'Unexpected extra content.');
            return true;
        });
    }

    public function apply(array $m, User $actor): void
    {
        $this->check($actor->is_owner === true, 'An owner actor is required.');
        $this->check(hash_file('sha256', $m['_archive_path']) === $m['_archive_sha256'] && hash_file('sha256', $m['_manifest_path']) === $m['_manifest_sha256'], 'Source/manifest changed after approval.');
        $created = [];
        $temporary = [];
        $reader = app(DocxReader::class);
        $zip = new ZipArchive;
        $this->check($zip->open($m['_archive_path']) === true, 'Cannot open original ZIP.');
        try {
            DB::transaction(function () use ($m, $actor, $zip, &$created, &$temporary) {
                Collection::whereKey(13)->lockForUpdate()->firstOrFail();
                if ($this->inspect($m)) { return; }
                foreach (['original.zip' => $m['_archive_path'], 'manifest.json' => $m['_manifest_path']] as $name => $source) {
                    $path = Storage::disk('sources')->path('imports/13/bekasyar-'.self::ARCHIVE_SHA.'/'.$name);
                    $this->archive($source, $path, $created);
                }
                foreach ($m['documents'] as $d) {
                    $bytes = $zip->getFromName($d['filename']);
                    $this->check(is_string($bytes) && hash('sha256', $bytes) === $d['sha256'], 'Original member checksum mismatch.');
                    $temp = tempnam(getenv('SHELF_TEST_TMP') ?: '/home/shelf/tmp', 'bekasyar-docx-');
                    $this->check($temp !== false, 'Cannot create private temporary file.');
                    $temporary[] = $temp;
                    chmod($temp, 0600);
                    $this->check(file_put_contents($temp, $bytes) === strlen($bytes), 'Source extraction failed.');
                    app()->instance(DocxReader::class, new ReviewedBekasyarReader($d));
                    $destination = Storage::disk('sources')->path('imports/13/'.$d['id'].'.docx');
                    $this->check(! file_exists($destination) && ! is_link($destination), 'Source destination already exists.');
                    // Only this exact attempt's new destination may be cleaned up.
                    $created[] = $destination;
                    app(ImportWordDocument::class)->import(13, $actor, [
                        'id' => $d['id'], 'path' => $temp, 'filename' => $d['filename'], 'sha256' => $d['sha256'],
                    ]);
                }
                $this->inspect($m);
            });
        } catch (Throwable $error) {
            // Preserve files if a database commit succeeded despite a transport error.
            if (! WordImport::whereIn('id', array_column($m['documents'], 'id'))->exists()) {
                foreach (array_reverse($created) as $path) {
                    if (is_file($path) && ! unlink($path)) { throw new RuntimeException('Import failed and source cleanup failed.', previous: $error); }
                }
            }
            throw $error;
        } finally {
            app()->instance(DocxReader::class, $reader);
            $zip->close();
            foreach ($temporary as $path) { if (is_file($path)) { unlink($path); } }
        }
    }

    public function reverse(array $m): void
    {
        DB::transaction(function () use ($m) {
            $this->check($this->inspect($m), 'No intact import to reverse.');
            foreach ($m['documents'] as $d) {
                foreach (WordImport::findOrFail($d['id'])->item_ids as $id) { Poem::findOrFail($id)->delete(); }
            }
            // Originals, audit, existing bin works and all unrelated records remain.
        });
    }

    private function archive(string $source, string $path, array &$created): void
    {
        if (! is_dir(dirname($path))) { $this->check(mkdir(dirname($path), 0700, true), 'Cannot create private archive directory.'); }
        $this->check(! file_exists($path) && ! is_link($path), 'Archive destination already exists.');
        $out = fopen($path, 'xb');
        $this->check($out !== false, 'Cannot archive source.');
        $created[] = $path;
        chmod($path, 0600);
        $in = fopen($source, 'rb');
        try { $this->check($in !== false && stream_copy_to_stream($in, $out) !== false, 'Archive copy failed.'); }
        finally { if (is_resource($in)) { fclose($in); } fclose($out); }
        $this->check(hash_file('sha256', $source) === hash_file('sha256', $path), 'Archive checksum mismatch.');
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) { throw new RuntimeException($message); }
    }
}
