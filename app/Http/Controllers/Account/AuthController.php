<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Reader;
use App\Models\User;
use App\Services\Accounts\AccountActions;
use App\Services\Accounts\GoogleIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AccountActions $actions) {}

    public function config()
    {
        $enabled = (bool) config('reader_auth.enabled');
        $google = $enabled && GoogleIdentity::enabled();

        return response()->json(['enabled' => $enabled, 'google_enabled' => $google,
            'google_web_client_id' => $google ? config('reader_auth.google_web_client_id') : null,
            'owner_testing_only' => ! config('reader_auth.public_registration')]);
    }

    private function email(Request $request): string
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        return $request->validate(['email' => ['required', 'email:rfc', 'max:255']])['email'];
    }

    public static function passwordRules(): array
    {
        return ['bail', 'required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols(),
            function ($attribute, $value, $fail) {
                if (strlen($value) > 72) {
                    $fail('Use at most 72 password bytes.');
                }
            }];
    }

    private function permitRegistration(string $email): void
    {
        abort_unless(config('reader_auth.public_registration') || User::where('is_owner', true)->whereRaw('LOWER(email) = ?', [$email])->exists(), 403, 'Registration is currently limited to owner testing.');
    }

    public function register(Request $request)
    {
        $email = $this->email($request);
        $data = $request->validate(['name' => ['nullable', 'string', 'max:100'], 'password' => self::passwordRules()]);
        $this->permitRegistration($email);
        $reader = Reader::firstOrCreate(['email' => $email], ['name' => $data['name'] ?? null]);
        if ($reader->wasRecentlyCreated) {
            $reader->forceFill(['password' => $data['password']])->save();
        }
        if (! $reader->email_verified_at) {
            $this->actions->send($reader, 'verify');
        }

        return response()->json(['message' => 'Check your email to verify your account, or sign in if you already have an account.'], 202);
    }

    public function login(Request $request)
    {
        $email = $this->email($request);
        $data = $request->validate(['password' => ['required', 'string', 'max:256']]);
        $reader = Reader::where('email', $email)->first();
        // The dummy hash keeps unknown-email checks on the password-hashing path.
        $hash = $reader?->password ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (! Hash::check($data['password'], $hash) || ! $reader?->password) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }

        return $this->session($reader);
    }

    private function session(Reader $reader)
    {
        return DB::transaction(function () use ($reader) {
            $reader = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
            $reader->tokens()->where('expires_at', '<=', now())->delete();
            $token = $reader->createToken('mobile', ['reader'], now()->addDays(30));

            return response()->json(['user' => $reader->profile(), 'token' => $token->plainTextToken]);
        });
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()->profile()]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    public function resend(Request $request)
    {
        if (! $request->user()->email_verified_at) {
            $this->actions->send($request->user(), 'verify');
        }

        return response()->json(['message' => 'Check your email.'], 202);
    }

    public function forgot(Request $request)
    {
        $email = $this->email($request);
        if ($reader = Reader::where('email', $email)->first()) {
            $this->actions->send($reader, 'reset');
        }

        return response()->json(['message' => 'If the account exists, an email has been sent.'], 202);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string', 'max:256'], 'password' => self::passwordRules()]);
        DB::transaction(function () use ($request, $data) {
            $reader = Reader::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if (! $reader->password || ! Hash::check($data['current_password'], $reader->password)) {
                throw ValidationException::withMessages(['current_password' => 'The password is incorrect.']);
            }
            $reader->forceFill(['password' => $data['password']])->save();
            $reader->tokens()->delete();
            DB::table('reader_account_actions')->where('reader_id', $reader->id)->delete();
        });

        return response()->noContent();
    }

    public function deleteRequest(Request $request)
    {
        $this->actions->send($request->user(), 'delete');

        return response()->json(['message' => 'Confirm deletion using the email we sent you.'], 202);
    }

    public function google(Request $request, GoogleIdentity $google)
    {
        abort_unless(GoogleIdentity::enabled(), 404);
        $data = $request->validate(['id_token' => ['required', 'string', 'max:16384']]);
        $claims = $google->verify($data['id_token']);
        $email = strtolower($claims['email']);
        $subject = hash('sha256', $claims['sub']);
        $reader = Reader::where('email', $email)->first();
        if (! $reader) {
            $this->permitRegistration($email);
            $reader = Reader::create(['email' => $email]);
            $reader->forceFill(['sign_in_method' => 'google'])->save();
        }
        abort_if($reader->google_subject_hash && ! hash_equals($reader->google_subject_hash, $subject), 409, 'Google identity does not match this account.');
        abort_if(Reader::where('google_subject_hash', $subject)->whereKeyNot($reader->id)->exists(), 409, 'Google identity is already linked.');
        $authoritative = str_ends_with($email, '@gmail.com') || filled($claims['hd'] ?? null);
        if (! $authoritative && ! $reader->google_subject_hash) {
            // Google explicitly recommends a fresh email challenge for external
            // email domains, where email_verified can reflect historical control.
            $this->actions->send($reader, 'google', $subject);

            return response()->json(['email_confirmation_required' => true], 202);
        }
        DB::transaction(function () use ($reader, $subject) {
            $locked = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->google_subject_hash && ! hash_equals($locked->google_subject_hash, $subject), 409);
            if (! $locked->email_verified_at) {
                // Defeat account pre-hijacking by discarding an unverified password.
                $locked->forceFill(['password' => null]);
                $locked->tokens()->delete();
                DB::table('reader_account_actions')->where('reader_id', $locked->id)->delete();
            }
            $locked->forceFill(['google_subject_hash' => $subject, 'email_verified_at' => now(),
                'sign_in_method' => $locked->password ? 'email_google' : 'google'])->save();
        });

        return $this->session($reader->fresh());
    }
}
