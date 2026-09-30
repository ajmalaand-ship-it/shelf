<?php

namespace App\Services\Accounts;

use Google\Auth\AccessToken;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use Illuminate\Validation\ValidationException;

class GoogleIdentity
{
    public function __construct(private ?AccessToken $verifier = null) {}

    public static function enabled(): bool
    {
        return filled(config('reader_auth.google_web_client_id'));
    }

    public function verify(string $idToken): array
    {
        abort_unless(self::enabled(), 404);
        try {
            $verifier = $this->verifier ?? new AccessToken(HttpHandlerFactory::build(new Client(['timeout' => 8, 'connect_timeout' => 5])));
            $claims = $verifier->verify($idToken, ['audience' => config('reader_auth.google_web_client_id')]);
            if (! is_array($claims) || ! in_array($claims['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
                || ($claims['aud'] ?? null) !== config('reader_auth.google_web_client_id')
                || ! is_int($claims['exp'] ?? null) || $claims['exp'] <= time()
                || ($claims['email_verified'] ?? false) !== true
                || ! is_string($claims['sub'] ?? null) || $claims['sub'] === ''
                || ! filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid identity');
            }
        } catch (\Throwable) {
            // Never log ID tokens, upstream response bodies or credentials.
            throw ValidationException::withMessages(['id_token' => 'Google sign-in could not be verified.']);
        }

        return $claims;
    }
}
