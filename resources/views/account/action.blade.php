@extends('account.layout')
@section('content')
@if($done)
<p>Confirmed. Return to Shelf. If you changed your password or deleted your account, your previous sessions have been signed out.</p>
<p dir="rtl">تایید شو. شېلف ته بېرته لاړ شئ. که پټنوم مو بدل کړی یا حساب مو ړنګ کړی وي، پخوانۍ ناستې مو وتړل شوې.</p>
@else
<h2>{{ ['verify'=>'Verify email / برېښنالیک تایید کړئ','reset'=>'Reset password / پټنوم بدل کړئ','delete'=>'Delete reader account / د لوستونکي حساب ړنګ کړئ','google'=>'Confirm Google sign-in / د ګوګل ننوتل تایید کړئ'][$purpose] }}</h2>
@if($purpose === 'delete')<p>This permanently removes your reader account, personal profile and sign-in sessions. Your owner/admin account is separate and will not be deleted. There are no purchase records in this version.</p><p dir="rtl">دا کار ستاسو د لوستونکي حساب، شخصي معلومات او ناستې ړنګوي. د مالک اداري حساب جلا دی او نه ړنګېږي.</p>@endif
<form method="post" action="{{ url('/account/'.$purpose) }}" autocomplete="off">@csrf
<input id="action-token" type="hidden" name="token" value="">
@if($purpose === 'reset')
<label>New password / نوی پټنوم<input type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></label>
<label>Confirm password / پټنوم بیا ولیکئ<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
<p>At least 12 characters, with uppercase and lowercase letters, a number and a symbol. Maximum 72 UTF-8 bytes.</p>
@endif
<button type="submit">Confirm / تایید کړئ</button>
</form>
<script>document.getElementById('action-token').value=window.location.hash.slice(1);</script>
<noscript>Enable JavaScript to confirm the secure email link.</noscript>
@endif
@endsection
