@extends('account.layout')
@section('content')
@if($done)
<p>Confirmed. Return to Shelf. If you changed your password or deleted your account, your previous sessions have been signed out.</p>

@else
<h2>{{ ['verify'=>'Verify email','reset'=>'Reset password','delete'=>'Delete reader account','google'=>'Confirm Google sign-in'][$purpose] }}</h2>
@if($purpose === 'delete')<p>This permanently removes your reader account, personal profile and sign-in sessions. Your owner/admin account is separate and will not be deleted. Access to your books is removed; transaction and accounting history is kept without your name or email. A new account does not automatically receive those purchases. Support can recover eligible purchases only after verifying the claimant and original store transaction; refunded or revoked books stay unavailable.</p>@endif
<form method="post" action="{{ url('/account/'.$purpose) }}" autocomplete="off">@csrf
<input id="action-token" type="hidden" name="token" value="">
@if($purpose === 'reset')
<label>New password<input type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></label>
<label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
<ul id="password-checks" aria-live="polite"></ul>
<script>
document.addEventListener('DOMContentLoaded',()=>{
const password=document.querySelector('[name="password"]'), confirm=document.querySelector('[name="password_confirmation"]');
function update(){const v=password.value; const checks=[['At least 12 characters',[...v].length>=12],['An uppercase letter',/\p{Lu}/u.test(v)],['A lowercase letter',/\p{Ll}/u.test(v)],['A number',/\p{N}/u.test(v)],['A symbol, such as ! or @',/[\p{S}\p{P}]/u.test(v)],['No more than 72 bytes',v.length>0&&new TextEncoder().encode(v).length<=72],['Passwords match',v.length>0&&v===confirm.value]];
const list=document.getElementById('password-checks'); list.replaceChildren(...checks.map(([label,ok])=>{const li=document.createElement('li');li.textContent=(ok?'✓ ':'○ ')+label;return li;}));}
password.addEventListener('input',update);confirm.addEventListener('input',update);update();
});
</script>
@endif
<button type="submit">Confirm</button>
</form>
<script>document.getElementById('action-token').value=window.location.hash.slice(1);</script>
<noscript>Enable JavaScript to confirm the secure email link.</noscript>
@endif
@endsection
