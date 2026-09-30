<?php

// Run only after the approved backup; never prints other .env values.
require dirname(__DIR__).'/vendor/autoload.php';

try {
    if (($argv[1] ?? '') !== '--after-backup' || posix_getpwuid(posix_geteuid())['name'] !== 'shelf') {
        throw new RuntimeException('Run as shelf after backup.');
    }
    $audience = $argv[2] ?? '';
    if (! preg_match('/^[a-z0-9-]+\.apps\.googleusercontent\.com$/', $audience)) {
        throw new RuntimeException('Invalid audience.');
    }
    $path = dirname(__DIR__).'/.env';
    $handle = fopen($path, 'r+');
    if (! $handle || ! flock($handle, LOCK_EX)) {
        throw new RuntimeException('Cannot lock configuration.');
    }
    $text = stream_get_contents($handle);
    $values = Dotenv\Dotenv::parse($text);
    if (parse_url($values['APP_URL'] ?? '', PHP_URL_HOST) !== 'shelf.services') {
        throw new RuntimeException('Unexpected domain.');
    }
    $line = 'GOOGLE_WEB_CLIENT_ID='.$audience;
    $text = preg_match('/^GOOGLE_WEB_CLIENT_ID=/m', $text)
        ? preg_replace_callback('/^GOOGLE_WEB_CLIENT_ID=.*$/m', fn () => $line, $text)
        : rtrim($text)."\n".$line."\n";
    rewind($handle);
    if (fwrite($handle, $text) !== strlen($text) || ! ftruncate($handle, strlen($text)) || ! fflush($handle)) {
        throw new RuntimeException('Cannot save configuration.');
    }
    flock($handle, LOCK_UN);
    fclose($handle);
    echo "Google web audience configured; no other settings changed.\n";
} catch (Throwable) {
    fwrite(STDERR, "Google configuration failed; stop and inspect privately.\n");
    exit(1);
}
