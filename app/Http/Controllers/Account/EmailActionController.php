<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Reader;
use App\Services\Accounts\AccountActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailActionController extends Controller
{
    public function page(string $purpose)
    {
        abort_unless(in_array($purpose, ['verify', 'reset', 'delete', 'google'], true), 404);

        return view('account.action', ['purpose' => $purpose, 'done' => false]);
    }

    public function consume(Request $request, string $purpose, AccountActions $actions)
    {
        abort_unless(in_array($purpose, ['verify', 'reset', 'delete', 'google'], true), 404);
        $data = $request->validate(['token' => ['required', 'string', 'size:64'],
            ...($purpose === 'reset' ? ['password' => AuthController::passwordRules()] : [])]);
        $actions->consume($data['token'], $purpose, function (Reader $reader, $entry) use ($purpose, $data, $actions) {
            if ($purpose === 'delete') {
                $actions->delete($reader);

                return;
            }
            if ($purpose === 'verify') {
                $reader->forceFill(['email_verified_at' => now()]);
            }
            if ($purpose === 'reset') {
                $reader->forceFill(['password' => $data['password'], 'email_verified_at' => now(),
                    'sign_in_method' => $reader->google_subject_hash ? 'email_google' : 'email']);
                $reader->tokens()->delete();
                DB::table('reader_account_actions')->where('reader_id', $reader->id)->delete();
            }
            if ($purpose === 'google') {
                abort_if(Reader::where('google_subject_hash', $entry->google_subject_hash)->whereKeyNot($reader->id)->exists(), 409);
                abort_if($reader->google_subject_hash && $reader->google_subject_hash !== $entry->google_subject_hash, 409);
                if (! $reader->email_verified_at) {
                    $reader->forceFill(['password' => null]);
                }
                $reader->forceFill(['google_subject_hash' => $entry->google_subject_hash, 'email_verified_at' => now(),
                    'sign_in_method' => $reader->password ? 'email_google' : 'google']);
                $reader->tokens()->delete();
                DB::table('reader_account_actions')->where('reader_id', $reader->id)->delete();
            }
            $reader->save();
        });
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Confirmed. Return to Shelf.']);
        }

        return view('account.action', ['purpose' => $purpose, 'done' => true]);
    }

    public function deletionRequest(Request $request, AccountActions $actions)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);
        if ($reader = Reader::where('email', strtolower(trim($data['email'])))->first()) {
            $actions->send($reader, 'delete');
        }

        return back()->with('sent', true);
    }
}
