<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ReaderAccountMail extends Mailable
{
    public function __construct(public string $purpose, public string $actionUrl) {}

    public function build(): self
    {
        return $this->subject(match ($this->purpose) {
            'verify' => 'Shelf — Verify your email / برېښنالیک تایید کړئ',
            'reset' => 'Shelf — Reset password / پټنوم بدل کړئ',
            'delete' => 'Shelf — Confirm account deletion / حساب ړنګول تایید کړئ',
            'google' => 'Shelf — Confirm Google sign-in / د ګوګل ننوتل تایید کړئ',
        })->view('account.email');
    }
}
