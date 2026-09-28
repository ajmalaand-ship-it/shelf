<?php

// Owner-run read-only check after backup and migrations. No secrets are printed.
use App\Support\BookCreditCheck;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    if (DB::connection()->getDatabaseName() !== 'shelf_app') {
        throw new RuntimeException('Unexpected database target.');
    }
    $report = BookCreditCheck::report();
    foreach ($report['books'] as $book) {
        echo json_encode($book, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }
    echo json_encode(['book_8_unknown_author_unchanged' => $report['book_8_unknown_author_unchanged']],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
    $valid = $report['valid'];
    echo $valid ? "PASS: exactly six approved books, all Pashto, with exact owner-approved credits.\n"
        : "REVIEW REQUIRED: book IDs, count, language, or exact credits differ from the owner decision.\n";
    exit($valid ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR, "Check failed; database and exception details withheld. No data changed.\n");
    exit(1);
}
