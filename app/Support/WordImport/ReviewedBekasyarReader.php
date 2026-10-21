<?php

namespace App\Support\WordImport;

use RuntimeException;

/** Scoped only to the explicitly approved original member; never the Admin reader. */
class ReviewedBekasyarReader extends DocxReader
{
    public function __construct(private array $document) {}

    public function read(string $path): array
    {
        if (! is_file($path) || hash_file('sha256', $path) !== $this->document['sha256']) {
            throw new RuntimeException('Reviewed original member mismatch.');
        }
        return ['sha256' => $this->document['sha256'], 'items' => $this->document['items'],
            'omissions' => [], 'warnings' => $this->document['warnings']];
    }
}
