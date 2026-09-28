<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static ?string $testStorage = null;

    public function createApplication()
    {
        if (self::$testStorage === null) {
            self::$testStorage = dirname(__DIR__).'/storage/framework/testing/isolated-'.bin2hex(random_bytes(8));
            foreach (['app/private', 'app/public', 'app/source', 'framework/views', 'framework/cache', 'framework/sessions', 'framework/testing', 'logs'] as $directory) {
                mkdir(self::$testStorage.'/'.$directory, 0700, true);
            }
            $directory = self::$testStorage;
            register_shutdown_function(fn () => (new Filesystem)->deleteDirectory($directory));
        }
        $_ENV['LARAVEL_STORAGE_PATH'] = self::$testStorage;

        return parent::createApplication();
    }
}
