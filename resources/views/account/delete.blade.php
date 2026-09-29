@extends('account.layout')
@section('content')
<h2>Delete your Shelf reader account</h2><h2 dir="rtl">د شېلف د لوستونکي حساب ړنګول</h2>
<p>Enter your account email. We send a confirmation link, valid for one hour. Your account is deleted only after you confirm. Your profile, authentication credentials and active sessions are removed; there are no purchase records to retain in this version. Restricted backups expire within the 14-backup retention cycle.</p>
<p dir="rtl">د حساب برېښنالیک ولیکئ. د تایید لینک درلېږو چې تر یوه ساعت پورې کار کوي. حساب، شخصي معلومات او ناستې یوازې ستاسو له تایید وروسته ړنګېږي.</p>
@if(session('sent'))<p>If the account exists, a confirmation email has been sent. / که حساب موجود وي، د تایید برېښنالیک لېږل شوی.</p>@endif
<form method="post" action="{{ url('/account/delete/request') }}">@csrf<label>Email / برېښنالیک<input name="email" type="email" required maxlength="255" autocomplete="email"></label><button>Request deletion / د ړنګولو غوښتنه</button></form>
<p>You can also request deletion from Account in the Shelf app. If you cannot access your email, contact <a href="mailto:ajmalaand@gmail.com">ajmalaand@gmail.com</a>.</p>
@endsection
