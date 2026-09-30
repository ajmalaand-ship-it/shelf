<?php

namespace App\Services\Accounts;

use App\Mail\ReaderAccountMail;
use App\Models\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountActions
{
    public function send(Reader $reader, string $purpose, ?string $googleSubjectHash = null): void
    {
        // Coalesce repeated requests without exposing whether an email exists.
        if (DB::table('reader_account_actions')->where('reader_id', $reader->id)->where('purpose', $purpose)
            ->where('created_at', '>', now()->subMinute())->exists()) {
            return;
        }
        $token = Str::random(64);
        $id = DB::table('reader_account_actions')->insertGetId([
            'reader_id' => $reader->id, 'purpose' => $purpose, 'token_hash' => hash('sha256', $token),
            'google_subject_hash' => $googleSubjectHash, 'expires_at' => now()->addHour(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        try {
            // URL fragments never enter the web-server request/access log.
            Mail::to($reader->email)->send(new ReaderAccountMail($purpose, url('/account/'.($purpose === 'delete' ? 'delete/confirm' : $purpose)).'#'.$token));
        } catch (\Throwable) {
            DB::table('reader_account_actions')->where('id', $id)->delete();
            abort(503, 'Email could not be sent. Please try again.');
        }
    }

    public function consume(string $token, string $purpose, callable $action): mixed
    {
        return DB::transaction(function () use ($token, $purpose, $action) {
            $entry = DB::table('reader_account_actions')->where('token_hash', hash('sha256', $token))
                ->where('purpose', $purpose)->where('expires_at', '>', now())->lockForUpdate()->first();
            if (! $entry) {
                throw ValidationException::withMessages(['token' => 'This link is invalid or has expired.']);
            }
            $reader = Reader::whereKey($entry->reader_id)->lockForUpdate()->firstOrFail();
            $result = $action($reader, $entry);
            DB::table('reader_account_actions')->where('reader_id', $reader->id)->where('purpose', $purpose)->delete();

            return $result;
        });
    }

    public function delete(Reader $reader): void
    {
        DB::transaction(function () use ($reader) {
            $reader = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
            $reader->tokens()->delete();
            DB::table('reader_account_actions')->where('reader_id', $reader->id)->delete();
            // Money records are retained unchanged. These nullable identifiers have no
            // FK to the deleted identity; no names/emails are stored in accounting.
            $reader->delete();
        });
    }
}
