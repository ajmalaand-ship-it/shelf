<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class ConfigureOwnerPreviewSecret extends Command
{
    protected $signature = 'poetry:configure-owner-preview-secret
        {--env-file= : Absolute environment-file path; defaults to the application .env}';

    protected $description = 'Generate the server-only owner-preview secret when it is absent';

    public function handle(): int
    {
        $path = $this->option('env-file') ?: base_path('.env');
        if (! is_string($path) || ! str_starts_with($path, '/') || ! is_file($path) || is_link($path)) {
            $this->error('A regular absolute environment-file path is required.');

            return self::FAILURE;
        }

        try {
            $contents = file_get_contents($path);
            if (! is_string($contents)) {
                throw new RuntimeException('Environment file is unreadable.');
            }
            if (preg_match('/^POETRY_OWNER_PREVIEW_SECRET=(.+)$/m', $contents, $match) === 1
                && trim($match[1]) !== '') {
                $this->info('Owner preview secret is already configured.');

                return self::SUCCESS;
            }

            $line = 'POETRY_OWNER_PREVIEW_SECRET='.bin2hex(random_bytes(32));
            if (preg_match('/^POETRY_OWNER_PREVIEW_SECRET=.*$/m', $contents) === 1) {
                $updated = preg_replace('/^POETRY_OWNER_PREVIEW_SECRET=.*$/m', $line, $contents, 1);
            } else {
                $updated = rtrim($contents)."\n\n$line\n";
            }
            if (! is_string($updated)) {
                throw new RuntimeException('Environment update failed.');
            }

            $temporary = $path.'.owner-preview-'.bin2hex(random_bytes(6));
            if (file_put_contents($temporary, $updated, LOCK_EX) === false) {
                throw new RuntimeException('Temporary environment file could not be written.');
            }
            chmod($temporary, 0600);
            if (! rename($temporary, $path)) {
                @unlink($temporary);
                throw new RuntimeException('Environment file could not be replaced.');
            }
            chmod($path, 0600);
        } catch (\Throwable) {
            $this->error('Owner preview secret could not be configured.');

            return self::FAILURE;
        }

        $this->info('Owner preview secret configured successfully.');

        return self::SUCCESS;
    }
}
