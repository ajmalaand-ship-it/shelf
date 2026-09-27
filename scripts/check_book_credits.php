<?php

// Owner-run read-only check after backup and migrations. No secrets are printed.
use App\Models\Collection;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    if (DB::connection()->getDatabaseName() !== 'shelf_app') {
        throw new RuntimeException('Unexpected database target.');
    }
    $books = Collection::with('credits.author')->whereIn('id', [1, 2, 3, 4, 5, 6])->orderBy('id')->get();
    $valid = $books->count() === 6;
    foreach ($books as $book) {
        $credits = $book->credits->map(fn ($credit) => [
            'role' => $credit->role, 'position' => $credit->position,
            'name' => $credit->author->name, 'slug' => $credit->author->slug,
        ])->all();
        echo json_encode([
            'id' => $book->id, 'title' => $book->title,
            'language' => $book->language, 'credits' => $credits,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $valid = $valid && $book->language === 'ps' && $book->credits->contains('role', 'author');
        if ($book->id === 6) {
            $valid = $valid && $book->credits->contains(fn ($credit) => $credit->role === 'author' && $credit->author->name === 'پروین پژواک')
                && $book->credits->contains(fn ($credit) => $credit->role === 'translator' && $credit->author->name === 'اجمل اند');
        }
    }
    echo $valid ? "PASS: six books, Pashto, credited authors, and book 6 attribution verified.\n"
        : "REVIEW REQUIRED: book count, language, or credits differ from the owner decision.\n";
    exit($valid ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR, "Check failed; database and exception details withheld. No data changed.\n");
    exit(1);
}
