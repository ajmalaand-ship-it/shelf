<?php

namespace App\Console\Commands;

use App\Models\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MoveBookCovers extends Command
{
    protected $signature = 'shelf:move-covers {--after-backup : Confirm a verified backup exists} {--dry-run : List only} {--restore-public : Roll back file relocation; requires matching old code before serving}';

    protected $description = 'Move only referenced book covers; preserve and list unreferenced files. Owner runs after backup.';

    public function handle(): int
    {
        if (! $this->option('dry-run') && ! $this->option('after-backup')) {
            $this->error('Run a verified backup first, then pass --after-backup.');

            return self::FAILURE;
        }
        $publicRoot = storage_path('app/public/covers');
        $privateRoot = Storage::disk('covers')->path('');
        $paths = Collection::withTrashed()->whereNotNull('cover_image')->pluck('cover_image')->unique()->values()->all();
        $public = Storage::build(['driver' => 'local', 'root' => $publicRoot, 'throw' => true]);
        $unreferenced = array_values(array_diff($public->allFiles(), $paths));
        sort($unreferenced);
        $this->info('Unreferenced public covers (untouched): '.count($unreferenced));
        foreach ($unreferenced as $path) {
            $this->line($path);
        }
        $reverse = (bool) $this->option('restore-public');
        $sourceRoot = $reverse ? $privateRoot : $publicRoot;
        $targetRoot = $reverse ? $publicRoot : $privateRoot;
        $plan = [];
        // Validate the entire plan before moving anything; retries are safe after interruption.
        foreach ($paths as $path) {
            if (! Collection::safeCoverPath($path)) {
                throw new RuntimeException('Unsafe cover path; nothing moved.');
            }
            $source = rtrim($sourceRoot, '/').'/'.$path;
            $target = rtrim($targetRoot, '/').'/'.$path;
            foreach ([$source, $target] as $candidate) {
                $cursor = $candidate;
                while ($cursor !== '/' && $cursor !== '.') {
                    if (is_link($cursor)) {
                        throw new RuntimeException('Symlink in cover path; nothing moved.');
                    }
                    $cursor = dirname($cursor);
                }
            }
            if (! is_file($source) && ! is_file($target)) {
                throw new RuntimeException('Referenced cover is missing: '.$path);
            }
            if (is_file($source) && is_file($target) && ! hash_equals(hash_file('sha256', $source), hash_file('sha256', $target))) {
                throw new RuntimeException('Cover destination differs; nothing moved: '.$path);
            }
            $plan[] = [$source, $target, $path];
        }
        foreach ($plan as [$source, $target, $path]) {
            if ($this->option('dry-run')) {
                $this->line('Planned: '.$path);

                continue;
            }
            if (is_file($source)) {
                if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0700, true) && ! is_dir(dirname($target))) {
                    throw new RuntimeException('Could not create cover directory.');
                }
                if (is_file($target)) {
                    if (! unlink($source)) {
                        throw new RuntimeException('Could not remove verified duplicate public cover.');
                    }
                } elseif (! rename($source, $target)) {
                    throw new RuntimeException('Cover move failed; rerun safely after investigating.');
                }
                chmod($target, $reverse ? 0644 : 0600);
            }
            $this->line(($reverse ? 'Public: ' : 'Private: ').$path);
        }

        return self::SUCCESS;
    }
}
