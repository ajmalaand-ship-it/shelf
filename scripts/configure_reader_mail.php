<?php

// Account-local, owner-approved rollout helper. Never prints credentials or
// subprocess errors (uapi errors can repeat arguments). Back up before running.
require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Process\Process;

function accountMailUapi(string $method, array $arguments): array
{
    $process = new Process(['/usr/local/cpanel/bin/uapi', '--output=json', 'Email', $method, ...$arguments]);
    $process->setTimeout(60);
    $process->run();
    $result = json_decode($process->getOutput(), true)['result'] ?? null;
    if (! $process->isSuccessful() || ($result['status'] ?? null) !== 1) {
        throw new RuntimeException('cPanel email operation failed: '.$method.'. No credentials shown.');
    }

    return $result['data'] ?? [];
}

try {
    if (($argv[1] ?? '') !== '--after-backup' || posix_getpwuid(posix_geteuid())['name'] !== 'shelf') {
        throw new RuntimeException('Run as shelf with --after-backup after a successful backup.');
    }
    $envPath = dirname(__DIR__).'/.env';
    $text = file_get_contents($envPath);
    $values = Dotenv\Dotenv::parse($text);
    $domain = parse_url($values['APP_URL'] ?? '', PHP_URL_HOST);
    if ($domain !== 'shelf.services') {
        throw new RuntimeException('Unexpected Shelf domain; stopped.');
    }
    $address = 'noreply@'.$domain;
    $mailboxes = accountMailUapi('list_pops', ['domain='.$domain]);
    $exists = count(array_filter($mailboxes, fn ($mailbox) => ($mailbox['email'] ?? '') === $address)) > 0;
    if ($exists && (($values['MAIL_USERNAME'] ?? '') !== $address || empty($values['MAIL_PASSWORD']))) {
        throw new RuntimeException('Mailbox already exists without its credentials in .env; stopped without resetting it.');
    }
    $password = $exists ? $values['MAIL_PASSWORD'] : bin2hex(random_bytes(32)).'aA!9';
    $updates = ['MAIL_MAILER' => 'smtp', 'MAIL_SCHEME' => 'smtps', 'MAIL_HOST' => 'mail.'.$domain,
        'MAIL_PORT' => '465', 'MAIL_USERNAME' => $address, 'MAIL_PASSWORD' => $password,
        'MAIL_FROM_ADDRESS' => $address, 'MAIL_FROM_NAME' => 'Shelf', 'MAIL_URL' => '',
        'READER_ACCOUNTS_ENABLED' => 'false', 'READER_PUBLIC_REGISTRATION' => 'false'];
    foreach ($updates as $key => $value) {
        $line = $key.'="'.addcslashes($value, '\\"$').'"';
        $text = preg_match('/^'.preg_quote($key, '/').'=/m', $text)
            ? preg_replace_callback('/^'.preg_quote($key, '/').'=.*$/m', fn () => $line, $text)
            : rtrim($text)."\n".$line."\n";
    }
    // Persist the only plaintext copy before creating the mailbox. If cPanel
    // fails, accounts remain disabled and the error is safe to report.
    $handle = fopen($envPath, 'c+');
    if (! $handle || ! flock($handle, LOCK_EX)) {
        throw new RuntimeException('Could not lock .env.');
    }
    chmod($envPath, 0600);
    if (fwrite($handle, $text) !== strlen($text) || ! ftruncate($handle, strlen($text)) || ! fflush($handle)) {
        throw new RuntimeException('Could not save mail configuration.');
    }
    flock($handle, LOCK_UN);
    fclose($handle);
    if (! $exists) {
        accountMailUapi('add_pop', ['email=noreply', 'domain='.$domain, 'password='.$password, 'quota=250', 'send_welcome_email=0']);
    }
    echo "Shelf noreply mailbox and SMTP configured; credentials retained only in .env. Accounts remain disabled pending verification.\n";
} catch (Throwable $error) {
    // Do not expose exception messages from libraries or cPanel.
    fwrite(STDERR, "Mail configuration failed; accounts remain disabled. Inspect the configuration without exposing credentials.\n");
    exit(1);
}
