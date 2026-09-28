<?php

// READ-ONLY: owner-run check after backup and migration; never prints secrets.
use App\Support\BookFoundationCheck;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    if (DB::connection()->getDatabaseName() !== 'shelf_app') {
        throw new RuntimeException('Unexpected database target.');
    }
    $report = BookFoundationCheck::report();
    foreach ($report['books'] as $book) {
        echo json_encode($book, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }
    echo 'Total content: '.$report['total_content'].PHP_EOL;
    echo $report['valid'] ? "PASS: six Pashto poetry books and 342 content items.\n"
        : "REVIEW REQUIRED: book identity, type, language or content count differs.\n";
    exit($report['valid'] ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR, "Check failed; details withheld to protect database credentials. No data changed.\n");
    exit(1);
}
