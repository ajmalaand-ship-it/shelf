<?php

namespace App\Services\Accounts;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AppleIdentity
{
    public static function configured(): bool
    {
        return filled(config('reader_auth.apple_client_id'))
            && filled(config('reader_auth.apple_team_id'))
            && filled(config('reader_auth.apple_key_id'))
            && is_readable(config('reader_auth.apple_private_key_path') ?? '');
    }

    public static function enabled(): bool
    {
        return config('reader_auth.apple_enabled') && config('reader_auth.apple_relay_ready') && self::configured();
    }

    public function verify(string $token, ?string $nonce = null): array
    {
        try {
            $jwks = Cache::remember('shelf.apple.public-keys', 3600, fn () => Http::timeout(8)->connectTimeout(5)->get('https://appleid.apple.com/auth/keys')->throw()->json());
            // Accept only RSA signing keys; never trust a token-supplied key URL.
            $jwks['keys'] = array_values(array_filter($jwks['keys'] ?? [], fn ($key) => ($key['kty'] ?? null) === 'RSA' && ($key['alg'] ?? null) === 'RS256'));
            $c = (array) JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'));
            if (($c['iss'] ?? null) !== 'https://appleid.apple.com'
                || ($c['aud'] ?? null) !== config('reader_auth.apple_client_id')
                || ! is_int($c['exp'] ?? null) || $c['exp'] <= time()
                || ! is_int($c['iat'] ?? null) || $c['iat'] > time() + 30
                || ! is_string($c['sub'] ?? null) || $c['sub'] === ''
                || ($nonce !== null && (! is_string($c['nonce'] ?? null) || ! hash_equals($nonce, $c['nonce'])))) {
                throw new \RuntimeException;
            }

            return $c;
        } catch (\Throwable) {
            throw ValidationException::withMessages(['id_token' => 'Apple sign-in could not be verified.']);
        }
    }

    private function secret(): string
    {
        abort_unless(self::configured(), 503);
        try {
            return JWT::encode(['iss' => config('reader_auth.apple_team_id'), 'iat' => time(),
                'exp' => time() + 300, 'aud' => 'https://appleid.apple.com',
                'sub' => config('reader_auth.apple_client_id')],
                file_get_contents(config('reader_auth.apple_private_key_path')), 'ES256', config('reader_auth.apple_key_id'));
        } catch (\Throwable) {
            abort(503, 'Apple sign-in is temporarily unavailable.');
        }
    }

    public function exchange(string $code, string $subject): string
    {
        try {
            $r = Http::asForm()->timeout(8)->connectTimeout(5)->post('https://appleid.apple.com/auth/token', [
                'client_id' => config('reader_auth.apple_client_id'), 'client_secret' => $this->secret(),
                'code' => $code, 'grant_type' => 'authorization_code'])->throw()->json();
            $claims = $this->verify($r['id_token'] ?? '');
            if (! hash_equals($subject, $claims['sub']) || ! is_string($r['refresh_token'] ?? null)
                || $r['refresh_token'] === '') {
                throw new \RuntimeException;
            }

            return $r['refresh_token'];
        } catch (\Throwable) {
            // Never log tokens, signing material or upstream response bodies.
            throw ValidationException::withMessages(['id_token' => 'Apple sign-in could not be verified. Please try again.']);
        }
    }

    public function revoke(string $token): void
    {
        try {
            Http::asForm()->timeout(8)->connectTimeout(5)->post('https://appleid.apple.com/auth/revoke', [
                'client_id' => config('reader_auth.apple_client_id'), 'client_secret' => $this->secret(),
                'token' => $token, 'token_type_hint' => 'refresh_token'])->throw();
        } catch (\Throwable) {
            abort(503, 'Apple access could not be revoked. Please try deletion again.');
        }
    }
}
