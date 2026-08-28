<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class VerifyOwnerArchive extends Command
{
    protected $signature = 'poetry:owner-archive-verify {archive}';

    protected $description = 'Verify checksum, readability, scope, and member checksums of an owner archive';

    public function handle(): int
    {
        $archive = realpath((string) $this->argument('archive'));
        if ($archive === false || ! is_file($archive)) {
            throw new RuntimeException('Owner archive does not exist.');
        }
        $sidecar = $archive.'.sha256';
        if (! is_file($sidecar)) {
            throw new RuntimeException('Owner archive checksum sidecar is missing.');
        }
        $expected = strtok(trim(File::get($sidecar)), ' ');
        if (! is_string($expected) || ! hash_equals($expected, hash_file('sha256', $archive))) {
            throw new RuntimeException('Owner archive checksum mismatch.');
        }

        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('Owner archive is unreadable.');
        }
        try {
            foreach (['database/database.sql', 'catalogue/catalogue.json', 'README.txt', 'SHA256SUMS'] as $required) {
                if ($zip->locateName($required) === false) {
                    throw new RuntimeException("Owner archive is missing {$required}.");
                }
            }

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                if ($name !== false && preg_match('~(^|/)(\.env(?:\.|$)|id_rsa|id_ed25519|.*\.key$)~i', $name)) {
                    throw new RuntimeException("Sensitive file is forbidden in owner archive: {$name}.");
                }
            }

            $manifest = $zip->getFromName('SHA256SUMS');
            foreach (array_filter(explode("\n", (string) $manifest)) as $line) {
                [$hash, $name] = explode('  ', $line, 2);
                $contents = $zip->getFromName($name);
                if ($contents === false || ! hash_equals($hash, hash('sha256', $contents))) {
                    throw new RuntimeException("Owner archive member checksum mismatch: {$name}.");
                }
            }
        } finally {
            $zip->close();
        }

        $this->info("Owner archive integrity and secret-exclusion checks passed: {$archive}");

        return self::SUCCESS;
    }
}
