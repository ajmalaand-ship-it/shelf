$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Windows.Forms
$ssh = (Get-Command ssh.exe -ErrorAction Stop).Source
$picker = New-Object System.Windows.Forms.OpenFileDialog
$picker.InitialDirectory = Join-Path $env:USERPROFILE 'Downloads'
$picker.Title = 'Select the NEW ShelfLogin Apple .p8 key'
$picker.Filter = 'Apple private key (*.p8)|*.p8'
$picker.Multiselect = $false
if ($picker.ShowDialog() -ne [System.Windows.Forms.DialogResult]::OK) {
    $picker.Dispose()
    throw 'Cancelled; nothing uploaded.'
}
$source = $picker.FileName
$picker.Dispose()
$name = [System.IO.Path]::GetFileName($source)
if ($name -cnotmatch '^[A-Za-z0-9][A-Za-z0-9_.-]*\.p8$') {
    throw 'Unexpected Apple filename; stopped without renaming or uploading.'
}
# Protect the original local file; do not create an extra key copy.
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent()
$acl = Get-Acl -LiteralPath $source
$acl.SetAccessRuleProtection($true, $false)
foreach ($rule in @($acl.Access)) { [void]$acl.RemoveAccessRuleSpecific($rule) }
$acl.SetOwner($identity.User)
$access = New-Object System.Security.AccessControl.FileSystemAccessRule(
    $identity.User, 'FullControl', 'Allow')
$acl.AddAccessRule($access)
Set-Acl -LiteralPath $source -AclObject $acl
$stream = [System.IO.File]::Open($source, 'Open', 'Read', 'Read')
try {
    if ($stream.Length -lt 1 -or $stream.Length -gt 16384) {
        throw 'Unexpected key file size; nothing uploaded.'
    }
    $size = $stream.Length
    $sha = [System.Security.Cryptography.SHA256]::Create()
    try { $hash = ([BitConverter]::ToString($sha.ComputeHash($stream))).Replace('-', '').ToLowerInvariant() }
    finally { $sha.Dispose() }
    $stream.Position = 0
    # Only this public receiver code is encoded; key bytes go through SSH stdin.
    $receiver = @'
import os, sys, stat, pwd, re, hashlib
name, size, expected = sys.argv[1], int(sys.argv[2]), sys.argv[3]
assert re.fullmatch(r'[A-Za-z0-9][A-Za-z0-9_.-]*\.p8', name)
assert 0 < size <= 16384
a = pwd.getpwnam('shelf')
directory = '/home/shelf/secrets/apple'
for d in ('/home/shelf/secrets', directory):
    s = os.lstat(d)
    assert stat.S_ISDIR(s.st_mode) and s.st_uid == a.pw_uid and s.st_gid == a.pw_gid and stat.S_IMODE(s.st_mode) == 0o700, 'Unsafe directory'
path = os.path.join(directory, name)
os.umask(0o077)
try:
    fd = os.open(path, os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_NOFOLLOW, 0o600)
except FileExistsError:
    sys.exit('REFUSED: original filename already exists; nothing overwritten.')
try:
    with os.fdopen(fd, 'wb') as target:
        os.fchown(target.fileno(), a.pw_uid, a.pw_gid)
        h, count = hashlib.sha256(), 0
        while True:
            data = sys.stdin.buffer.read(8192)
            if not data:
                break
            count += len(data)
            assert count <= size, 'Unexpected transfer size'
            h.update(data)
            target.write(data)
        assert count == size and h.hexdigest() == expected, 'Incomplete or mismatched transfer'
        target.flush()
        os.fsync(target.fileno())
        s = os.fstat(target.fileno())
        assert s.st_uid == a.pw_uid and s.st_gid == a.pw_gid and stat.S_IMODE(s.st_mode) == 0o600
except BaseException:
    os.unlink(path)
    sys.exit('Transfer failed; this new incomplete file was removed. No key contents printed.')
print('Upload verified: original filename preserved; shelf:shelf; mode 600.')
'@
    $encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($receiver))
    $remote = "python3 -c 'import base64;exec(base64.b64decode(`"$encoded`"))' $name $size $hash"
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
        try { $stream.CopyTo($process.StandardInput.BaseStream) }
        finally { $process.StandardInput.Close() }
        $process.WaitForExit()
        if ($process.ExitCode -ne 0) { throw 'SSH upload failed; do not overwrite or retry over an existing file.' }
    }
    finally { $process.Dispose() }
}
finally { $stream.Dispose() }
