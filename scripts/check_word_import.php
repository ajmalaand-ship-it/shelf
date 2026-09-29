<?php

// READ-ONLY: owner runs this after the Word import migration. No source text is printed.
use App\Models\WordImport;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    if (DB::connection()->getDatabaseName() !== 'shelf_app') {
        throw new RuntimeException('Unexpected database target.');
    }
    if (! Schema::hasColumns('word_imports', [
        'id', 'collection_id', 'imported_by', 'imported_by_name', 'imported_at',
        'original_filename', 'source_path', 'sha256', 'item_count', 'item_ids', 'warnings',
    ])) {
        throw new RuntimeException('Missing import audit schema.');
    }
    $checked = 0;
    foreach (WordImport::cursor() as $import) {
        $expectedPath = 'imports/'.$import->collection_id.'/'.$import->id.'.docx';
        if ($import->source_path !== $expectedPath || $import->item_count !== count($import->item_ids)) {
            throw new RuntimeException('Invalid import metadata.');
        }
        $path = Storage::disk('sources')->path($expectedPath);
        if (! is_file($path) || ! hash_equals($import->sha256, hash_file('sha256', $path))) {
            throw new RuntimeException('Original source checksum mismatch.');
        }
        $checked++;
    }
    echo "PASS: Word import schema ready; {$checked} archived source checksums verified.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Check failed. No data changed; exception details withheld to protect credentials.\n");
    exit(1);
}
