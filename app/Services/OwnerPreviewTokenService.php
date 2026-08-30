<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;

class OwnerPreviewTokenService
{
    public function issue(int $days): array
    {
        if ($days < 1 || $days > 30) {
            throw new RuntimeException('Token lifetime must be between 1 and 30 days.');
        }

        $expiresAt = now()->addDays($days)->startOfSecond();
        $payload = $expiresAt->getTimestamp().'.'.Str::random(32);

        return [
            'token' => 'v1.'.$payload.'.'.$this->signature($payload),
            'expires_at' => $expiresAt,
        ];
    }

    public function valid(?string $token): bool
    {
        if (! is_string($token) || strlen($token) > 256) {
            return false;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 4 || $parts[0] !== 'v1') {
            return false;
        }

        [$version, $expires, $nonce, $signature] = $parts;
        unset($version);
        if (! ctype_digit($expires) || strlen($nonce) !== 32 || ! ctype_alnum($nonce)) {
            return false;
        }
        if ((int) $expires <= now()->getTimestamp()) {
            return false;
        }

        return hash_equals($this->signature($expires.'.'.$nonce), $signature);
    }

    private function signature(string $payload): string
    {
        $secret = config('poetry.owner_preview_secret');
        if (! is_string($secret) || strlen($secret) < 32) {
            throw new RuntimeException('Owner preview is not configured.');
        }

        return hash_hmac('sha256', $payload, $secret);
    }
}
