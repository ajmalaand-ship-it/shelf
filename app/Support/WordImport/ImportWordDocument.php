<?php

namespace App\Support\WordImport;

use App\Models\Collection;
use App\Models\Poem;
use App\Models\User;
use App\Models\WordImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ImportWordDocument
{
    public function import(int $bookId, User $actor, array $preview, bool $acknowledged = false): WordImport
    {
        $createdPath = null;
        try {
            return DB::transaction(function () use ($bookId, $actor, $preview, $acknowledged, &$createdPath): WordImport {
                $book = Collection::whereKey($bookId)->lockForUpdate()->firstOrFail();
                // Book lock serializes concurrent submissions; the unique preview ID is consumed once.
                if ($existing = WordImport::find($preview['id'])) {
                    abort_unless($existing->collection_id === $bookId && $existing->imported_by === $actor->id, 403);

                    return $existing;
                }
                $document = app(DocxReader::class)->read($preview['path']);
                if (! hash_equals($preview['sha256'], $document['sha256'])) {
                    throw ValidationException::withMessages(['document' => 'The file changed after preview. Preview it again before importing.']);
                }
                if ($document['omissions'] && ! $acknowledged) {
                    throw ValidationException::withMessages(['acknowledge_omissions' => 'Confirm that you understand the listed content will not be imported.']);
                }
                if (! $document['items']) {
                    throw ValidationException::withMessages(['document' => 'There are no items to import.']);
                }
                $relative = 'imports/'.$book->id.'/'.$preview['id'].'.docx';
                $path = Storage::disk('sources')->path($relative);
                try {
                    if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0700, true) && ! is_dir(dirname($path))) {
                        throw new RuntimeException('Could not create the private source directory.');
                    }
                    // A pre-existing file is never overwritten or adopted by a new attempt.
                    if (file_exists($path) || is_link($path)) {
                        throw new RuntimeException('The source destination already exists. Preview the upload again.');
                    }
                    $output = fopen($path, 'xb');
                    if ($output === false) {
                        throw new RuntimeException('Could not archive the source document.');
                    }
                    $createdPath = $path;
                    try {
                        chmod($path, 0600);
                        $input = fopen($preview['path'], 'rb');
                        if ($input === false) {
                            throw new RuntimeException('The temporary upload is no longer readable.');
                        }
                        try {
                            $this->copySource($input, $output);
                        } finally {
                            fclose($input);
                        }
                    } finally {
                        fclose($output);
                    }
                    if (! hash_equals($document['sha256'], hash_file('sha256', $path))) {
                        throw new RuntimeException('The archived source checksum does not match.');
                    }
                    $ids = [];
                    foreach ($document['items'] as $item) {
                        $record = $book->poems()->create([
                            'title' => $item['title'], 'body' => $item['body'],
                            'excerpt' => '', 'is_active' => false, 'is_free_sample' => false,
                            'layout_mode' => Poem::LAYOUT_SOURCE,
                        ]);
                        $ids[] = $record->id;
                    }

                    return WordImport::create([
                        'id' => $preview['id'], 'collection_id' => $book->id,
                        'imported_by' => $actor->id, 'imported_by_name' => $actor->name,
                        'imported_at' => now(), 'original_filename' => $preview['filename'],
                        'source_path' => $relative, 'sha256' => $document['sha256'],
                        'item_count' => count($ids), 'item_ids' => $ids, 'warnings' => $document['warnings'],
                    ]);
                } catch (Throwable $error) {
                    // Clean up while still holding the book lock, before another attempt can run.
                    $this->removeAttemptFile($createdPath);
                    throw $error;
                }
            });
        } catch (Throwable $error) {
            // Also cover a commit failure; preserve a file if the commit did succeed.
            if ($createdPath !== null) {
                DB::transaction(function () use ($bookId, $preview, &$createdPath): void {
                    Collection::withTrashed()->whereKey($bookId)->lockForUpdate()->firstOrFail();
                    if (! WordImport::whereKey($preview['id'])->exists()) {
                        $this->removeAttemptFile($createdPath);
                    }
                });
            }
            throw $error;
        }
    }

    protected function copySource($input, $output): void
    {
        if (stream_copy_to_stream($input, $output) === false) {
            throw new RuntimeException('Could not archive the complete source document.');
        }
    }

    private function removeAttemptFile(?string &$path): void
    {
        if ($path === null) {
            return;
        }
        if (is_file($path) && ! unlink($path)) {
            throw new RuntimeException('Failed to remove an incomplete import file.');
        }
        $directory = dirname($path);
        if (is_dir($directory) && count(scandir($directory)) === 2) {
            rmdir($directory);
        }
        $path = null;
    }
}
