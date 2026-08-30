<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OwnerPreviewTokenCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_writes_protected_flutter_config_without_printing_token(): void
    {
        config(['poetry.owner_preview_secret' => str_repeat('s', 64)]);
        $path = sys_get_temp_dir().'/pitswal-owner-preview-command-test-'.bin2hex(random_bytes(6)).'.json';

        try {
            $this->artisan('poetry:owner-preview-token', ['--days' => 7, '--output' => $path])
                ->expectsOutput('Owner preview configuration written successfully.')
                ->assertSuccessful();

            $this->assertFileExists($path);
            $this->assertSame(0600, fileperms($path) & 0777);
            $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $this->assertTrue($data['OWNER_PREVIEW']);
            $this->assertIsString($data['OWNER_PREVIEW_TOKEN']);
            $this->assertStringNotContainsString($data['OWNER_PREVIEW_TOKEN'], Artisan::output());
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
