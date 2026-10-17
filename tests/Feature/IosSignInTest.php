<?php

namespace Tests\Feature;

use App\Models\Reader;
use App\Models\User;
use App\Services\Accounts\AccountActions;
use App\Services\Accounts\AppleIdentity;
use App\Services\Accounts\DeletionJournal;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IosSignInTest extends TestCase
{
    use RefreshDatabase;

    private string $private = '';

    private string $keyPath = '';

    private array $claims;

    private ?string $exchangeSubject = null;

    private int $tokenStatus = 200;

    private int $revokeStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Cache::flush();
        $rsa = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($rsa, $this->private);
        $details = openssl_pkey_get_details($rsa);
        $b64 = fn ($s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $jwks = ['keys' => [['kty' => 'RSA', 'kid' => 'synthetic', 'alg' => 'RS256',
            'n' => $b64($details['rsa']['n']), 'e' => $b64($details['rsa']['e'])]]];
        $ec = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        openssl_pkey_export($ec, $signing);
        $this->keyPath = getenv('SHELF_TEST_TMP').'/synthetic-apple-'.bin2hex(random_bytes(6)).'.p8';
        file_put_contents($this->keyPath, $signing);
        config(['reader_auth.enabled' => true, 'reader_auth.apple_enabled' => true,
            'reader_auth.apple_relay_ready' => true, 'reader_auth.apple_client_id' => 'services.shelf.app',
            'reader_auth.apple_team_id' => 'SYNTHETIC', 'reader_auth.apple_key_id' => 'test',
            'reader_auth.apple_private_key_path' => $this->keyPath]);
        $this->claims = ['iss' => 'https://appleid.apple.com', 'aud' => 'services.shelf.app',
            'exp' => time() + 3600, 'iat' => time(), 'sub' => 'apple-subject',
            'email' => 'private@privaterelay.appleid.com', 'email_verified' => 'true'];
        Http::fake(['appleid.apple.com/auth/keys' => Http::response($jwks),
            'appleid.apple.com/auth/token' => fn () => Http::response(['id_token' => $this->jwt($this->exchangeSubject ? ['sub' => $this->exchangeSubject] : []), 'refresh_token' => 'synthetic-refresh'], $this->tokenStatus),
            'appleid.apple.com/auth/revoke' => fn () => Http::response('', $this->revokeStatus)]);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->keyPath && is_file($this->keyPath)) {
                unlink($this->keyPath);
            }
        } finally {
            parent::tearDown();
        }
    }

    private function jwt(array $changes = []): string
    {
        return JWT::encode(array_replace($this->claims, $changes), $this->private, 'RS256', 'synthetic');
    }

    private function login(array $changes = [])
    {
        $challenge = $this->postJson('/api/auth/apple-challenge')->assertOk()->json();

        return $this->postJson('/api/auth/apple', ['challenge' => $challenge['challenge'],
            'id_token' => $this->jwt(array_replace(['nonce' => hash('sha256', $challenge['nonce'])], $changes)),
            'authorization_code' => 'synthetic-code']);
    }

    public function test_relay_login_repeat_subject_and_owner_separation(): void
    {
        User::factory()->create(['email' => $this->claims['email'], 'is_owner' => true]);
        $first = $this->login()->assertOk()->assertJsonPath('user.sign_in_method', 'apple')->assertJsonMissingPath('user.apple_refresh_token');
        $reader = Reader::firstOrFail();
        $this->assertFalse($reader->is_owner);
        $this->assertTrue($first->json('user.email_verified'));
        $this->assertSame('synthetic-refresh', $reader->apple_refresh_token);
        $this->assertNotSame('synthetic-refresh', DB::table('readers')->value('apple_refresh_token'));
        unset($this->claims['email'], $this->claims['email_verified']);
        $this->login()->assertOk()->assertJsonPath('user.id', $reader->id);
        $this->assertDatabaseCount('readers', 1);
    }

    public function test_same_email_never_links_or_modifies_existing_reader(): void
    {
        $reader = Reader::create(['email' => $this->claims['email']]);
        $reader->forceFill(['password' => 'Existing!Password123'])->save();
        $password = $reader->password;
        $this->login()->assertConflict();
        $this->assertSame($password, $reader->fresh()->password);
        $this->assertNull($reader->fresh()->apple_subject_hash);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/auth/token'));
    }

    public function test_challenge_expiry_and_replay_rejected(): void
    {
        $c = $this->postJson('/api/auth/apple-challenge')->json();
        $payload = ['challenge' => $c['challenge'], 'id_token' => $this->jwt(['nonce' => hash('sha256', $c['nonce'])]), 'authorization_code' => 'code'];
        $this->postJson('/api/auth/apple', $payload)->assertOk();
        $this->postJson('/api/auth/apple', $payload)->assertUnprocessable();
        $c = $this->postJson('/api/auth/apple-challenge')->json();
        $this->travel(6)->minutes();
        $payload['challenge'] = $c['challenge'];
        $this->postJson('/api/auth/apple', $payload)->assertUnprocessable();
    }

    public function test_nonce_signature_issuer_audience_expiry_and_subject_validation(): void
    {
        $apple = app(AppleIdentity::class);
        $nonce = str_repeat('a', 64);
        foreach ([['nonce' => 'wrong'], ['aud' => 'wrong'], ['iss' => 'wrong'], ['exp' => time() - 1], ['sub' => ''], ['iat' => time() + 90]] as $change) {
            try {
                $apple->verify($this->jwt(array_replace(['nonce' => $nonce], $change)), $nonce);
                $this->fail('Invalid identity accepted');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $parts = explode('.', $this->jwt(['nonce' => $nonce]));
        $parts[2] = rtrim(strtr(base64_encode(str_repeat('x', 256)), '+/', '-_'), '=');
        $this->expectException(ValidationException::class);
        $apple->verify(implode('.', $parts), $nonce);
    }

    public function test_code_subject_mismatch_and_provider_failure_issue_no_session(): void
    {
        $this->exchangeSubject = 'other';
        $this->login()->assertUnprocessable();
        $this->assertDatabaseCount('readers', 0);
        $this->tokenStatus = 503;
        $this->login()->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_first_login_requires_a_verified_valid_email(): void
    {
        foreach ([['email_verified' => false], ['email' => 'invalid'], ['email' => ['bad']]] as $change) {
            $this->login($change)->assertUnprocessable();
        }
        $this->assertDatabaseCount('readers', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_disabled_or_missing_configuration_fails_closed(): void
    {
        config(['reader_auth.apple_relay_ready' => false]);
        $this->getJson('/api/auth/config')->assertJsonPath('apple_enabled', false);
        $this->postJson('/api/auth/apple-challenge')->assertNotFound();
        config(['reader_auth.apple_relay_ready' => true, 'reader_auth.apple_private_key_path' => null]);
        $this->postJson('/api/auth/apple', [])->assertNotFound();
    }

    public function test_deletion_revoke_failure_keeps_reader_and_tokens_then_success_erases_identity(): void
    {
        $this->login()->assertOk();
        $reader = Reader::firstOrFail();
        $this->revokeStatus = 503;
        try {
            app(AccountActions::class)->delete($reader);
            $this->fail('Deletion unexpectedly succeeded');
        } catch (HttpException $e) {
            $this->assertSame(503, $e->getStatusCode());
        }
        $this->assertNotNull($reader->fresh());
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->mock(DeletionJournal::class, fn ($m) => $m->shouldReceive('record')->andReturn(['record_id' => str_repeat('a', 32)]));
        $this->revokeStatus = 200;
        app(AccountActions::class)->delete($reader);
        $this->assertNull($reader->fresh());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/auth/revoke') && $r['token_type_hint'] === 'refresh_token');
    }

    public function test_schema_reverse_preserves_existing_reader(): void
    {
        $reader = Reader::create(['email' => 'existing@example.test']);
        $m = require database_path('migrations/2026_10_11_120000_add_apple_reader_identity.php');
        $m->down();
        $this->assertNotNull($reader->fresh());
        $this->assertFalse(Schema::hasColumn('readers', 'apple_subject_hash'));
        $m->up();
        $this->assertTrue(Schema::hasTable('apple_login_challenges'));
    }
}
