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

class ImportWordDocument
{
    public function import(int $bookId, User $actor, array $preview): WordImport
    {
        return DB::transaction(function () use ($bookId, $actor, $preview): WordImport {
            $book = Collection::whereKey($bookId)->lockForUpdate()->firstOrFail();
            if ($existing = WordImport::find($preview['id'])) {
                abort_unless($existing->collection_id === $bookId && $existing->imported_by === $actor->id, 403);

                return $existing;
            }
            $document = app(DocxReader::class)->read($preview['path']);
            if (! hash_equals($preview['sha256'], $document['sha256'])) {
                throw ValidationException::withMessages(['document' => 'The file changed after preview. Preview it again before importing.']);
            }
            if (! $document['items']) {
                throw ValidationException::withMessages(['document' => 'There are no items to import.']);
            }
            $relative = 'imports/'.$book->id.'/'.$preview['id'].'.docx';
            $path = Storage::disk('sources')->path($relative);
            if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0700, true) && ! is_dir(dirname($path))) {
                throw new RuntimeException('Could not create the private source directory.');
            }
            // Exclusive creation: never replace or delete an archived source document.
            // A failed database transaction may retain its original for a safe retry.
            if (! is_file($path)) {
                $output = fopen($path, 'xb');
                if ($output === false) {
                    throw new RuntimeException('Could not archive the source document.');
                }
                try {
                    chmod($path, 0600);
                    $input = fopen($preview['path'], 'rb');
                    if ($input === false) {
                        throw new RuntimeException('The temporary upload is no longer readable.');
                    }
                    try {
                        if (stream_copy_to_stream($input, $output) === false) {
                            throw new RuntimeException('Could not archive the complete source document.');
                        }
                    } finally {
                        fclose($input);
                    }
                } finally {
                    fclose($output);
                }
            }
            if (! hash_equals($document['sha256'], hash_file('sha256', $path))) {
                throw new RuntimeException('The archived source checksum does not match. No items were imported.');
            }
            $ids = [];
            foreach ($document['items'] as $item) {
                $record = $book->poems()->create([
                    'title' => $item['title'], 'body' => $item['body'],
                    // No public excerpt or sample is guessed from private source text.
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
        });
    }
}
