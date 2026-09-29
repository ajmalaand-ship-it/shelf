@extends('account.layout')
@section('content')
<h2>Delete your Shelf reader account</h2>
<p>Enter your account email. We send a confirmation link, valid for one hour. Your account is deleted only after you confirm. Your profile, authentication credentials and active sessions are removed; there are no purchase records to retain in this version. Restricted backups expire within the 14-backup retention cycle.</p>

@if(session('sent'))<p>If the account exists, a confirmation email has been sent.</p>@endif
<form method="post" action="{{ url('/account/delete/request') }}">@csrf<label>Email<input name="email" type="email" required maxlength="255" autocomplete="email"></label><button>Request deletion</button></form>
<p>You can also request deletion from Account in the Shelf app. If you cannot access your email, contact <a href="mailto:ajmalaand@gmail.com">ajmalaand@gmail.com</a>.</p>
@endsection
