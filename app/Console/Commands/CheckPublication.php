<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class CheckPublication extends Command
{
    protected $signature = 'shelf:check-publication';

    protected $description = 'Read-only status/cover/owner report and direct HTTPS curl checks.';

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        if (parse_url($base, PHP_URL_SCHEME) !== 'https') {
            $this->error('APP_URL must use HTTPS.');

            return self::FAILURE;
        }
        $valid = true;
        $owners = User::where('is_owner', true)->count();
        $this->line('Owner flag count: '.$owners);
        $valid = $owners === 1;
        $books = Collection::withTrashed()->get();
        foreach ($books as $book) {
            $private = $book->cover_image && Storage::disk('covers')->exists($book->cover_image);
            $legacy = $book->cover_image && is_file(storage_path('app/public/covers/'.$book->cover_image));
            $this->line(json_encode(['book_id' => $book->id, 'status' => $book->status,
                'cover_location' => $private ? Storage::disk('covers')->path($book->cover_image) : 'MISSING',
                'legacy_public_copy' => (bool) $legacy], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if ($book->cover_image && (! $private || $legacy)) {
                $valid = false;
            }
            $published = $book->isPublished();
            $requests = [
                '/api/collections/'.rawurlencode($book->slug) => $published ? 200 : 404,
                '/api/collections/'.rawurlencode($book->slug).'/poems' => $published ? 200 : 404,
                '/media/covers/'.$book->id => $published && $private ? 200 : 404,
            ];
            if ($book->cover_image) {
                $requests['/storage/covers/'.implode('/', array_map('rawurlencode', explode('/', $book->cover_image)))] = 404;
            }
            foreach ($requests as $path => $expected) {
                $curl = new Process(['curl', '--silent', '--show-error', '--max-time', '20', '--output', '/dev/null', '--write-out', '%{http_code}', $base.$path]);
                $curl->run();
                $actual = (int) trim($curl->getOutput());
                $pass = $curl->isSuccessful() && $actual === $expected;
                $valid = $valid && $pass;
                $this->line(($pass ? 'PASS' : 'FAIL').' GET '.$base.$path.' expected='.$expected.' actual='.$actual);
            }
        }

        return $valid ? self::SUCCESS : self::FAILURE;
    }
}
