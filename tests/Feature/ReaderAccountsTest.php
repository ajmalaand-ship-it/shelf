<?php

namespace Tests\Feature;

use App\Mail\ReaderAccountMail;
use App\Models\Collection;
use App\Models\Reader;
use App\Models\User;
use App\Services\Accounts\AccountActions;
use App\Services\Accounts\GoogleIdentity;
use Firebase\JWT\JWT;
use Google\Auth\AccessToken;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReaderAccountsTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Synthetic!Pass123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['reader_auth.enabled' => true, 'reader_auth.public_registration' => true]);
        Mail::fake();
    }

    private function reader(string $email = 'reader@example.test', bool $verified = true): Reader
    {
        $reader = Reader::create(['email' => $email]);
        $reader->forceFill(['password' => self::PASSWORD, 'email_verified_at' => $verified ? now() : null])->save();

        return $reader;
    }

    private function token(Reader $reader): string
    {
        return $reader->createToken('test', ['reader'], now()->addDays(30))->plainTextToken;
    }

    private function bearer(string $token): array
    {
        app('auth')->forgetGuards();

        return ['Authorization' => 'Bearer '.$token];
    }

    private function action(string $purpose): string
    {
        $mail = Mail::sent(ReaderAccountMail::class, fn ($mail) => $mail->purpose === $purpose)->last();
        $this->assertNotNull($mail);

        return parse_url($mail->actionUrl, PHP_URL_FRAGMENT);
    }

    public function test_registration_is_minimal_hashed_and_separate_from_owner(): void
    {
        $owner = User::factory()->create(['email' => 'reader@example.test', 'is_owner' => true]);
        $this->postJson('/api/auth/register', ['email' => 'READER@example.test', 'name' => 'Reader', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD, 'is_owner' => true])->assertAccepted();
        $reader = Reader::firstOrFail();
        $this->assertFalse($reader->is_owner);
        $this->assertTrue(Hash::check(self::PASSWORD, $reader->password));
        $this->assertNotEquals(self::PASSWORD, $reader->password);
        $this->assertNull($reader->email_verified_at);
        $this->assertTrue($owner->fresh()->is_owner);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $token = $this->action('verify');
        $this->assertDatabaseHas('reader_account_actions', ['token_hash' => hash('sha256', $token)]);
        $this->get('/account/verify')->assertOk();
        $this->assertNull($reader->fresh()->email_verified_at);
        $this->postJson('/account/verify', ['token' => $token])->assertOk();
        $this->assertNotNull($reader->fresh()->email_verified_at);
        $this->postJson('/account/verify', ['token' => $token])->assertUnprocessable();
        $this->postJson('/api/auth/register', ['email' => $reader->email, 'password' => 'Other!Password123', 'password_confirmation' => 'Other!Password123'])->assertAccepted();
        $this->assertTrue(Hash::check(self::PASSWORD, $reader->fresh()->password));
    }

    public function test_strong_passwords_and_registration_open_to_any_email(): void
    {
        foreach (['short', 'alllowercase123!', 'NoNumbersHere!', 'NoSymbol123456', str_repeat('Aa1!', 19)] as $index => $password) {
            $this->postJson('/api/auth/register', ['email' => "r$index@example.test", 'password' => $password, 'password_confirmation' => $password])->assertUnprocessable();
        }
        config(['reader_auth.public_registration' => false]);
        $this->postJson('/api/auth/register', ['email' => 'outside@example.test', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertAccepted();
        User::factory()->create(['email' => 'owner@example.test', 'is_owner' => true]);
        $this->postJson('/api/auth/register', ['email' => 'owner@example.test', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertAccepted();
    }

    public function test_login_profile_and_logout_revoke_every_device_without_owner_access(): void
    {
        $reader = $this->reader();
        $this->postJson('/api/auth/login', ['email' => $reader->email, 'password' => 'wrong'])->assertUnprocessable();
        $login = $this->postJson('/api/auth/login', ['email' => $reader->email, 'password' => self::PASSWORD])->assertOk()->assertJsonMissingPath('user.password')->assertJsonMissingPath('user.google_subject_hash');
        $token = $login->json('token');
        $second = $this->token($reader);
        $this->getJson('/api/auth/me', $this->bearer($token))->assertOk()->assertJsonPath('user.id', $reader->id);
        $this->get('/admin', $this->bearer($token))->assertRedirect('/admin/login');
        $this->postJson('/api/auth/logout', [], $this->bearer($token))->assertNoContent();
        foreach ([$token, $second] as $revoked) {
            $this->getJson('/api/auth/me', $this->bearer($revoked))->assertUnauthorized();
        }
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_readers_cannot_use_owner_session_or_reader_tokens_for_books(): void
    {
        $reader = $this->reader();
        $token = $this->token($reader);
        $book = Collection::create(['title' => 'Private book', 'status' => 'draft', 'language' => 'ps']);
        $item = $book->poems()->create(['body' => 'Unchanged source', 'excerpt' => '', 'is_active' => true, 'sample_mode' => 'none']);
        foreach (['/api/collections/'.$book->slug, '/api/collections/'.$book->slug.'/poems', '/api/poems/'.$item->id, '/api/poems/'.$item->id.'/audio', '/media/covers/'.$book->id] as $path) {
            $this->getJson($path, $this->bearer($token))->assertNotFound();
        }
        $this->getJson('/api/owner-preview/collections', $this->bearer($token))->assertUnauthorized();
        $this->actingAs($reader, 'web')->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_owner' => true]), 'web')->getJson('/api/auth/me', ['Authorization' => ''])->assertUnauthorized();
    }

    public function test_reset_is_generic_one_use_expiring_and_revokes_tokens(): void
    {
        $reader = $this->reader();
        $old = $this->token($reader);
        $known = $this->postJson('/api/auth/forgot-password', ['email' => $reader->email])->assertAccepted()->json();
        $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.test'])->assertAccepted()->assertExactJson($known);
        $token = $this->action('reset');
        $this->postJson('/account/verify', ['token' => $token])->assertUnprocessable();
        $this->postJson('/account/reset', ['token' => $token, 'password' => 'weak', 'password_confirmation' => 'weak'])->assertUnprocessable();
        $this->postJson('/account/reset', ['token' => $token, 'password' => 'Changed!Password123', 'password_confirmation' => 'Changed!Password123'])->assertOk();
        $this->getJson('/api/auth/me', $this->bearer($old))->assertUnauthorized();
        $this->assertTrue(Hash::check('Changed!Password123', $reader->fresh()->password));
        $this->postJson('/account/reset', ['token' => $token, 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertUnprocessable();
        app(AccountActions::class)->send($reader, 'verify');
        $expired = $this->action('verify');
        $this->travel(61)->minutes();
        $this->postJson('/account/verify', ['token' => $expired])->assertUnprocessable();
    }

    public function test_password_change_requires_current_password_and_revokes_sessions(): void
    {
        $reader = $this->reader();
        $token = $this->token($reader);
        $data = ['current_password' => 'wrong', 'password' => 'Changed!Password123', 'password_confirmation' => 'Changed!Password123'];
        $this->postJson('/api/auth/password', $data, $this->bearer($token))->assertUnprocessable();
        $data['current_password'] = self::PASSWORD;
        $this->postJson('/api/auth/password', $data, $this->bearer($token))->assertNoContent();
        $this->getJson('/api/auth/me', $this->bearer($token))->assertUnauthorized();
        $this->assertTrue(Hash::check($data['password'], $reader->fresh()->password));
    }

    public function test_public_and_app_deletion_require_email_confirmation_and_remove_all_personal_rows(): void
    {
        $owner = User::factory()->create(['email' => 'reader@example.test', 'is_owner' => true]);
        $reader = $this->reader();
        $token = $this->token($reader);
        $this->get('/account/delete')->assertOk()->assertSee('Delete your Shelf reader account');
        $this->post('/account/delete/request', ['email' => 'unknown@example.test'])->assertRedirect()->assertSessionHas('sent', true);
        Mail::assertNothingSent();
        $this->post('/account/delete/request', ['email' => $reader->email])->assertRedirect();
        $this->postJson('/api/auth/delete-request', [], $this->bearer($token))->assertAccepted();
        $this->assertDatabaseCount('readers', 1);
        $this->get('/account/delete/confirm')->assertOk();
        $this->postJson('/account/delete', ['token' => str_repeat('a', 64)])->assertUnprocessable();
        $this->postJson('/account/delete', ['token' => $this->action('delete')])->assertOk();
        foreach (['readers', 'reader_account_actions', 'personal_access_tokens'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertNotNull($owner->fresh());
        $this->getJson('/api/auth/me', $this->bearer($token))->assertUnauthorized();
    }

    public function test_verification_resend_is_coalesced_and_mail_failure_is_safe(): void
    {
        $reader = $this->reader(verified: false);
        $token = $this->token($reader);
        $this->postJson('/api/auth/verify/resend', [], $this->bearer($token))->assertAccepted();
        $this->postJson('/api/auth/verify/resend', [], $this->bearer($token))->assertAccepted();
        Mail::assertSentCount(1);
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Synthetic transport failure'));
        $this->postJson('/api/auth/forgot-password', ['email' => $reader->email])->assertStatus(503);
        $this->assertDatabaseMissing('reader_account_actions', ['purpose' => 'reset']);
    }

    public function test_disabled_google_and_disabled_accounts_fail_closed_and_rate_limits_apply(): void
    {
        config(['reader_auth.google_web_client_id' => null, 'reader_auth.google_android_client_id' => null]);
        $this->getJson('/api/auth/config')->assertOk()->assertJsonPath('google_enabled', false)->assertJsonPath('google_web_client_id', null);
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertNotFound();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/api/auth/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertStatus(429);
        config(['reader_auth.enabled' => false]);
        $this->postJson('/api/auth/register', [])->assertStatus(503);
        $this->getJson('/api/auth/config')->assertJsonPath('enabled', false);
    }

    private function google(array $claims): void
    {
        config(['reader_auth.google_web_client_id' => 'web.test', 'reader_auth.google_android_client_id' => 'android.test']);
        $this->mock(GoogleIdentity::class)->shouldReceive('verify')->andReturn($claims);
    }

    public function test_web_audience_alone_enables_google_and_fake_tokens_are_rejected(): void
    {
        config(['reader_auth.google_web_client_id' => 'web.test', 'reader_auth.google_android_client_id' => null]);
        $this->getJson('/api/auth/config')->assertOk()->assertJsonPath('google_enabled', true)->assertJsonPath('google_web_client_id', 'web.test');
        $this->postJson('/api/auth/google', ['id_token' => 'fake'])->assertUnprocessable();
        $this->assertDatabaseCount('readers', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_google_links_only_verified_identity_and_defeats_unverified_account_pre_hijack(): void
    {
        $reader = $this->reader('reader@gmail.com', false);
        $old = $this->token($reader);
        $this->google(['email' => $reader->email, 'email_verified' => true, 'sub' => 'subject']);
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertOk()->assertJsonPath('user.sign_in_method', 'google');
        $this->assertNull($reader->fresh()->password);
        $this->getJson('/api/auth/me', $this->bearer($old))->assertUnauthorized();
        $this->assertEquals(hash('sha256', 'subject'), $reader->fresh()->google_subject_hash);
        $reader->forceFill(['password' => self::PASSWORD])->save();
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertOk()->assertJsonPath('user.sign_in_method', 'email_google');
        $this->google(['email' => $reader->email, 'email_verified' => true, 'sub' => 'wrong-subject']);
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertConflict();
    }

    public function test_external_google_email_requires_a_fresh_email_confirmation(): void
    {
        $reader = $this->reader();
        $this->google(['email' => $reader->email, 'email_verified' => true, 'sub' => 'external']);
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertAccepted()->assertJsonPath('email_confirmation_required', true)->assertJsonMissingPath('token');
        $this->assertNull($reader->fresh()->google_subject_hash);
        $this->postJson('/account/google', ['token' => $this->action('google')])->assertOk();
        $this->assertTrue(Hash::check(self::PASSWORD, $reader->fresh()->password));
        $this->postJson('/api/auth/google', ['id_token' => 'synthetic'])->assertOk();
    }

    public function test_google_cryptographic_validation_rejects_bad_signature_audience_issuer_expiry_and_email(): void
    {
        config(['reader_auth.google_web_client_id' => 'web.test', 'reader_auth.google_android_client_id' => 'android.test']);
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private);
        $details = openssl_pkey_get_details($key);
        $encode = fn ($v) => rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
        $jwks = ['keys' => [['kty' => 'RSA', 'kid' => 'test', 'alg' => 'RS256', 'use' => 'sig', 'n' => $encode($details['rsa']['n']), 'e' => $encode($details['rsa']['e'])]]];
        $verifier = new GoogleIdentity(new AccessToken(fn () => new Response(200, [], json_encode($jwks))));
        $claims = ['iss' => 'https://accounts.google.com', 'aud' => 'web.test', 'sub' => 'test', 'exp' => time() + 3600, 'iat' => time(), 'email' => 'reader@gmail.com', 'email_verified' => true];
        $this->assertEquals($claims, $verifier->verify(JWT::encode($claims, $private, 'RS256', 'test')));
        foreach ([['aud' => 'wrong'], ['iss' => 'evil'], ['exp' => time() - 3600], ['email_verified' => false], ['email_verified' => 'true'], ['email' => 'invalid'], ['sub' => '']] as $change) {
            try {
                $verifier->verify(JWT::encode(array_replace($claims, $change), $private, 'RS256', 'test'));
                $this->fail('Accepted invalid identity');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $bad = JWT::encode($claims, $private, 'RS256', 'test');
        $parts = explode('.', $bad);
        $parts[2] = $encode(str_repeat('x', 256));
        $this->expectException(ValidationException::class);
        $verifier->verify(implode('.', $parts));
    }

    public function test_migration_is_reversible_without_touching_owner_users(): void
    {
        $owner = User::factory()->create(['is_owner' => true]);
        $migration = require database_path('migrations/2026_09_29_120000_create_reader_accounts.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('readers'));
        $this->assertNotNull($owner->fresh());
        $migration->up();
        $this->assertTrue(Schema::hasTable('readers'));
    }
}
