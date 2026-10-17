<?php
// Owner-authorized operational tool, not an application deployment.
// Never prints recipient, credentials, SMTP transcript or exception details.
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '0');
$attempt = null;
$state = null;
try {
    if (! in_array($argv[1] ?? '', ['--preflight', '--check', '--send-once'], true)
        || posix_getpwuid(posix_geteuid())['name'] !== 'shelf') {
        throw new RuntimeException;
    }
    $directory = '/home/shelf/secrets/apple';
    $identity = posix_getpwnam('shelf');
    foreach (['/home/shelf/secrets', $directory] as $path) {
        $s = lstat($path);
        if (! $s || ($s['mode'] & 0170000) !== 0040000 || ($s['mode'] & 0777) !== 0700
            || $s['uid'] !== $identity['uid'] || $s['gid'] !== $identity['gid']) {
            throw new RuntimeException;
        }
    }
    $root = '/home/shelf/apps/shelf';
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (App\Support\Staging::active() || parse_url(config('app.url'), PHP_URL_HOST) !== 'shelf.services'
        || config('reader_auth.apple_enabled') || config('reader_auth.apple_relay_ready')
        || App\Services\Accounts\AppleIdentity::enabled()
        || config('purchases.production_enabled') || config('play_sync.enabled')
        || config('mail.default') !== 'smtp'
        || config('mail.mailers.smtp.transport') !== 'smtp'
        || config('mail.from.address') !== 'noreply@shelf.services') {
        throw new RuntimeException;
    }
    if (($argv[1] ?? '') === '--preflight') {
        echo "Preflight passed: production configured SMTP sender; Apple flags false; real checkout and sync OFF. No email sent.\n";
        exit(0);
    }
    $path = $directory.'/owner-relay-address.txt';
    $s = lstat($path);
    if (! $s || ($s['mode'] & 0170000) !== 0100000 || ($s['mode'] & 0777) !== 0600
        || $s['uid'] !== $identity['uid'] || $s['gid'] !== $identity['gid']
        || $s['nlink'] !== 1 || $s['size'] < 1 || $s['size'] > 254) {
        throw new RuntimeException;
    }
    $input = fopen($path, 'rb');
    $opened = fstat($input);
    if ($opened['dev'] !== $s['dev'] || $opened['ino'] !== $s['ino']) {
        throw new RuntimeException;
    }
    $address = stream_get_contents($input, 255);
    fclose($input);
    if (! preg_match('/\A[A-Za-z0-9._%+-]+@privaterelay\.appleid\.com\z/D', $address)) {
        throw new RuntimeException;
    }
    if (($argv[1] ?? '') === '--check') {
        echo "Relay file verified: shelf:shelf; mode 600; expected relay format; address withheld. No email sent.\n";
        exit(0);
    }
    // Resolve the existing mailer before consuming the single permitted attempt.
    $mailer = Illuminate\Support\Facades\Mail::mailer();
    if (! $mailer->getSymfonyTransport() instanceof Symfony\Component\Mailer\Transport\Smtp\SmtpTransport) {
        throw new RuntimeException;
    }
    umask(0077);
    $attempt = fopen($directory.'/relay-test-attempt.json', 'x');
    if (! $attempt) {
        throw new RuntimeException; // Never retry when any attempt file exists.
    }
    $state = ['attempted_at_utc' => gmdate('Y-m-d\TH:i:s\Z'), 'status' => 'attempt_started',
        'inbox_receipt' => 'unconfirmed', 'apple_enabled' => false, 'apple_relay_ready' => false];
    $save = static function () use ($attempt, &$state): void {
        $data = json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
        rewind($attempt);
        if (! ftruncate($attempt, 0) || fwrite($attempt, $data) !== strlen($data)
            || ! fflush($attempt) || ! fsync($attempt)) {
            throw new RuntimeException;
        }
    };
    $save();
    $sent = $mailer->raw('This is the owner-only Shelf email delivery check. No action is required.',
        static fn ($message) => $message->to($address)->subject('Shelf relay delivery check'));
    if ($sent === null) {
        throw new RuntimeException;
    }
    $state['status'] = 'smtp_accepted';
    $state['accepted_at_utc'] = gmdate('Y-m-d\TH:i:s\Z');
    $save();
    fclose($attempt);
    $attempt = null;
    echo "One Shelf relay test email accepted by configured SMTP service. Inbox receipt UNCONFIRMED. Apple flags remain false. No automatic resend.\n";
} catch (Throwable) {
    if (is_resource($attempt)) {
        // Preserve the attempt even if acceptance is uncertain; never remove it.
        $state['status'] = 'failed_or_acceptance_uncertain';
        $state['finished_at_utc'] = gmdate('Y-m-d\TH:i:s\Z');
        @rewind($attempt);
        @ftruncate($attempt, 0);
        @fwrite($attempt, json_encode($state)."\n");
        @fflush($attempt);
        @fsync($attempt);
        @fclose($attempt);
    }
    fwrite(STDERR, "Relay check stopped. No private details printed; no automatic resend. Any existing attempt file must be preserved.\n");
    exit(1);
}
