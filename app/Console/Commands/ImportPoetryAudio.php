<?php

namespace App\Console\Commands;

use App\Models\Poem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class ImportPoetryAudio extends Command
{
    protected $signature = 'poetry:audio-import
        {directory : Owner-upload directory, relative to the private audio inbox}
        {manifest=source/owner-recording-manifests/tsapo-ke-anzorona.json : Private recording manifest}
        {--apply : Attach validated recordings}';

    protected $description = 'Validate and safely attach an owner batch of original poem recordings';

    /** @var array<string> */
    private const ALLOWED_MIME_TYPES = ['audio/mp4', 'audio/x-m4a', 'video/mp4', 'application/mp4'];

    public function handle(): int
    {
        $directory = $this->resolveInboxDirectory((string) $this->argument('directory'));
        $manifest = $this->readManifest((string) $this->argument('manifest'));
        $expected = collect($manifest['recordings'] ?? [])->keyBy('expected_filename');

        if ($expected->isEmpty() || $expected->count() !== collect($manifest['recordings'])->pluck('sequence')->unique()->count()) {
            throw new RuntimeException('Recording manifest is empty or contains duplicate sequences.');
        }

        $files = collect(File::files($directory));
        $unknown = $files->reject(fn ($file): bool => $expected->has($file->getFilename()));
        if ($unknown->isNotEmpty()) {
            throw new RuntimeException('Unknown recording filename: '.$unknown->first()->getFilename());
        }

        $validated = $files->map(function ($file) use ($expected, $manifest): array {
            $entry = $expected->get($file->getFilename());
            $this->validateFile($file->getPathname());
            $poem = Poem::query()->findOrFail($entry['poem_id']);
            if ($poem->collection_id !== $manifest['collection']['id'] || $poem->sort_order !== $entry['sequence']) {
                throw new RuntimeException("Poem identity conflict for {$file->getFilename()}.");
            }

            $hash = hash_file('sha256', $file->getPathname());
            $target = sprintf(
                'collections/%s/%03d-%d-%s.m4a',
                $manifest['collection']['slug'],
                $entry['sequence'],
                $poem->id,
                substr($hash, 0, 12),
            );

            if ($poem->audio_path) {
                if ($poem->audio_path === $target && Storage::disk('audio')->exists($target)
                    && hash_equals($hash, hash('sha256', Storage::disk('audio')->get($target)))) {
                    return compact('poem', 'target') + ['source' => $file->getPathname(), 'duration' => $poem->audio_duration_seconds, 'already_attached' => true];
                }

                throw new RuntimeException("Poem sequence {$entry['sequence']} already has a recording; nothing was replaced.");
            }

            return compact('poem', 'target') + [
                'source' => $file->getPathname(),
                'duration' => $this->duration($file->getPathname()),
                'already_attached' => false,
            ];
        });

        if (! $this->option('apply')) {
            $this->info("Validated {$validated->count()} recording(s); use --apply to attach them.");

            return self::SUCCESS;
        }

        $attached = 0;
        foreach ($validated as $item) {
            if ($item['already_attached']) {
                continue;
            }
            DB::transaction(function () use ($item, &$attached): void {
                $poem = Poem::query()->lockForUpdate()->findOrFail($item['poem']->id);
                if ($poem->audio_path) {
                    throw new RuntimeException("Poem sequence {$poem->sort_order} acquired a recording; nothing was replaced.");
                }

                $stream = fopen($item['source'], 'rb');
                if ($stream === false) {
                    throw new RuntimeException('Could not read validated recording.');
                }
                try {
                    Storage::disk('audio')->put($item['target'], $stream);
                } finally {
                    fclose($stream);
                }

                try {
                    $poem->update([
                        'audio_path' => $item['target'],
                        'audio_duration_seconds' => $item['duration'],
                    ]);
                    $attached++;
                } catch (\Throwable $exception) {
                    Storage::disk('audio')->delete($item['target']);
                    throw $exception;
                }
            });
        }

        $this->info("Attached {$attached} new recording(s); ".($validated->count() - $attached).' already matched exactly.');

        return self::SUCCESS;
    }

    private function resolveInboxDirectory(string $argument): string
    {
        $root = (string) config('poetry.audio_inbox_path');
        File::ensureDirectoryExists($root, 0700, true);
        $root = realpath($root);
        $candidate = str_starts_with($argument, '/') ? $argument : $root.'/'.$argument;
        $directory = realpath($candidate);
        if ($root === false || $directory === false || ! is_dir($directory)
            || ($directory !== $root && ! str_starts_with($directory, $root.DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('Audio import directory must remain inside the private System C audio inbox.');
        }

        return $directory;
    }

    private function readManifest(string $argument): array
    {
        $path = str_starts_with($argument, '/') ? $argument : storage_path('app/'.$argument);
        $sourceRoot = realpath(storage_path('app/source'));
        $path = realpath($path);
        if ($sourceRoot === false || $path === false || ! str_starts_with($path, $sourceRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Recording manifest must remain in private System C source storage.');
        }

        return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function validateFile(string $path): void
    {
        if (is_link($path) || filesize($path) === false || filesize($path) > 50 * 1024 * 1024) {
            throw new RuntimeException('Recording is a link, unreadable, or exceeds 50 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new RuntimeException("Recording MIME type is not allowed: {$mime}.");
        }
    }

    private function duration(string $path): ?int
    {
        $process = new Process([
            '/usr/bin/ffprobe', '-v', 'error', '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1', $path,
        ]);
        $process->setTimeout(15);
        $process->run();
        if (! $process->isSuccessful() || ! is_numeric(trim($process->getOutput()))) {
            return null;
        }

        return max(0, (int) round((float) trim($process->getOutput())));
    }
}
