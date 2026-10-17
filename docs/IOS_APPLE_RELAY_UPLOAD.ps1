$ErrorActionPreference = 'Stop'
$ssh = (Get-Command ssh.exe -ErrorAction Stop).Source
$secret = Read-Host 'Paste your Shelf Apple relay address (hidden; Enter to upload)' -AsSecureString
$pointer = [IntPtr]::Zero
$address = $null
$payload = $null
try {
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secret)
    $address = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    if ($address.Length -gt 254 -or $address -cnotmatch '\A[A-Za-z0-9._%+-]+@privaterelay\.appleid\.com\z') {
        throw 'Stopped: enter only the Shelf Sign in with Apple relay address. No address printed or uploaded.'
    }
    $payload = [Text.Encoding]::UTF8.GetBytes($address)
    # Only public receiver code appears in arguments; address bytes use SSH stdin.
    $receiver = @'
import os,sys,stat,pwd,re
try:
    a=pwd.getpwnam('shelf')
    directory='/home/shelf/secrets/apple'
    for d in ('/home/shelf/secrets',directory):
        s=os.lstat(d)
        assert stat.S_ISDIR(s.st_mode) and s.st_uid==a.pw_uid and s.st_gid==a.pw_gid and stat.S_IMODE(s.st_mode)==0o700
    data=sys.stdin.buffer.read(256)
    assert 0<len(data)<=254 and re.fullmatch(rb'[A-Za-z0-9._%+-]+@privaterelay\.appleid\.com',data)
    path=directory+'/owner-relay-address.txt'
    os.umask(0o077)
    fd=os.open(path,os.O_WRONLY|os.O_CREAT|os.O_EXCL|os.O_NOFOLLOW,0o600)
except FileExistsError:
    sys.exit('REFUSED: destination exists; nothing overwritten. Do not send an address in chat.')
except BaseException:
    sys.exit('Transfer refused: input or private directory check failed. No address printed.')
try:
    with os.fdopen(fd,'wb') as target:
        os.fchown(target.fileno(),a.pw_uid,a.pw_gid)
        assert target.write(data)==len(data)
        target.flush()
        os.fsync(target.fileno())
        s=os.fstat(target.fileno())
        assert s.st_uid==a.pw_uid and s.st_gid==a.pw_gid and stat.S_IMODE(s.st_mode)==0o600
    with open(path,'rb') as saved:
        assert saved.read(256)==data
except BaseException:
    os.unlink(path)
    sys.exit('Transfer failed; only this new incomplete file was removed. No address printed.')
print('Relay upload verified: shelf:shelf; mode 600; address withheld. No email sent.')
'@
    $encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($receiver))
    $remote = "python3 -c 'import base64;exec(base64.b64decode(`"$encoded`"))'"
    $start = New-Object System.Diagnostics.ProcessStartInfo
    $start.FileName = $ssh
    $remoteArgument = '"' + $remote.Replace('"', '\"') + '"'
    $start.Arguments = '-T -o StrictHostKeyChecking=yes root@157.250.199.106 ' + $remoteArgument
    $start.UseShellExecute = $false
    $start.RedirectStandardInput = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $start
    try {
        [void]$process.Start()
        try { $process.StandardInput.BaseStream.Write($payload, 0, $payload.Length) }
        finally { $process.StandardInput.Close() }
        $process.WaitForExit()
        if ($process.ExitCode -ne 0) { throw 'Upload failed/refused. Do not retry over an existing file or paste the address in chat.' }
    }
    finally { $process.Dispose() }
}
finally {
    if ($payload) { [Array]::Clear($payload, 0, $payload.Length) }
    $address = $null
    if ($pointer -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }
    $secret.Dispose()
}
