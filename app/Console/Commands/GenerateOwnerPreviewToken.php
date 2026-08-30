<?php

namespace App\Console\Commands;

use App\Services\OwnerPreviewTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class GenerateOwnerPreviewToken extends Command
{
    protected $signature = 'poetry:owner-preview-token
        {--days= : Token lifetime in days}
        {--output= : Protected output file for Flutter dart defines}';

    protected $description = 'Write a temporary owner-preview Flutter configuration without displaying its token';

    public function handle(OwnerPreviewTokenService $tokens): int
    {
        $output = $this->option('output');
        $days = (int) ($this->option('days') ?: config('poetry.owner_preview_token_days', 7));
        if (! is_string($output) || $output === '' || ! str_starts_with($output, '/')) {
            $this->error('A valid absolute --output path is required.');

            return self::FAILURE;
        }

        try {
            $issued = $tokens->issue($days);
            $parent = dirname($output);
            if (! is_dir($parent) || ! is_writable($parent) || is_link($output)) {
                throw new RuntimeException('Output location is not writable or safe.');
            }
            $json = json_encode([
                'OWNER_PREVIEW' => true,
                'OWNER_PREVIEW_TOKEN' => $issued['token'],
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            File::put($output, $json."\n", true);
            chmod($output, 0600);
        } catch (\Throwable $error) {
            if (is_file($output)) {
                File::delete($output);
            }
            $this->error('Owner preview token could not be written.');

            return self::FAILURE;
        }

        $this->info('Owner preview configuration written successfully.');
        $this->line('Expires: '.$issued['expires_at']->toIso8601String());
        $this->line('Output: '.$output);

        return self::SUCCESS;
    }
}
