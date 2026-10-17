@extends('account.layout')
@section('content')
<h2>Delete your Shelf reader account</h2>
<p>Enter your account email. We send a confirmation link, valid for one hour. Your account is deleted only after you confirm. Your profile, authentication credentials and active sessions are removed. Your access to bought books is removed; transaction and accounting history is retained without your name or email. Creating a new account does not move purchases. Verified support-assisted purchase recovery is available; email matching alone is insufficient and refunded or revoked purchases stay unavailable. Local backups retain 14 copies; OneDrive normally retains 90 days, with a reported exception for the last verified recovery set. Protected deletion records prevent old backups restoring your access. RevenueCat metadata removal is retryable; necessary transaction evidence and Google’s own records remain. Apple deleted-account purchase recovery is not yet available. See Privacy for retention and exceptions.</p>
<p>Our approved policy is to retain accounting and tax-supporting records for three years after the relevant tax return is actually filed, and longer for affected records required by applicable law, unresolved disputes or unpaid author balances.</p>
        <p>Only the minimum purchase proof needed for continuing purchased-book recovery rights is to remain after financial detail can be safely removed. Automatic financial expiry is not implemented: existing financial and recovery records currently remain retained. The 90-day log and backup periods do not apply to financial history.</p>

@if(session('sent'))<p>If the account exists, a confirmation email has been sent.</p>@endif
<form method="post" action="{{ url('/account/delete/request') }}">@csrf<label>Email<input name="email" type="email" required maxlength="255" autocomplete="email"></label><button>Request deletion</button></form>
<p>You can also request deletion from Account in the Shelf app. If you cannot access your email, contact <a href="mailto:ajmalaand@gmail.com">ajmalaand@gmail.com</a>.</p>
@endsection
