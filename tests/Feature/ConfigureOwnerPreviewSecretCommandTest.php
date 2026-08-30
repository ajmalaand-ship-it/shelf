<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigureOwnerPreviewSecretCommandTest extends TestCase
{
    public function test_command_generates_secret_without_printing_it_and_is_idempotent(): void
    {
        $path = sys_get_temp_dir().'/pitswal-owner-preview-env-'.bin2hex(random_bytes(6));
        file_put_contents($path, "APP_NAME=Test\nPOETRY_OWNER_PREVIEW_SECRET=\n");

        try {
            $this->artisan('poetry:configure-owner-preview-secret', ['--env-file' => $path])
                ->expectsOutput('Owner preview secret configured successfully.')
                ->assertSuccessful();
            $contents = file_get_contents($path);
            preg_match('/^POETRY_OWNER_PREVIEW_SECRET=(.+)$/m', $contents, $match);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $match[1]);
            $this->assertSame(0600, fileperms($path) & 0777);

            $this->artisan('poetry:configure-owner-preview-secret', ['--env-file' => $path])
                ->expectsOutput('Owner preview secret is already configured.')
                ->assertSuccessful();
            $this->assertSame($contents, file_get_contents($path));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
