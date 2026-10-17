# Transfer the EXISTING Shelf Codemagic App Store Connect upload key only.
# No Apple/RevenueCat API call, token generation, activation or app upload.
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName Microsoft.VisualBasic
$ssh = (Get-Command ssh.exe -ErrorAction Stop).Source
$picker = New-Object System.Windows.Forms.OpenFileDialog
$picker.InitialDirectory = Join-Path $env:USERPROFILE 'Downloads'
$picker.Title = 'Existing Shelf Codemagic upload key - NOT ShelfLogin or ShelfPurchases'
$picker.Filter = 'Original Apple upload key (AuthKey_*.p8)|AuthKey_*.p8'
$picker.Multiselect = $false
try {
    if ($picker.ShowDialog() -ne [System.Windows.Forms.DialogResult]::OK) {
        throw 'Cancelled; nothing transferred.'
    }
    $source = $picker.FileName
}
finally { $picker.Dispose() }
$name = [IO.Path]::GetFileName($source)
if ($name -cnotmatch '^AuthKey_([A-Z0-9]{10})\.p8$') {
    throw 'Select the original AuthKey filename from the existing upload integration; do not rename a key.'
}
$keyId = $Matches[1]
if ($keyId -ceq '2VQ43945YB') { throw 'ShelfLogin is a sign-in key; nothing transferred.' }
# Reject reparse points in the selected file and every parent; leave its ACL/bytes unchanged.
$item = Get-Item -LiteralPath $source -Force
if ($item.PSIsContainer) { throw 'Select a regular .p8 file.' }
while ($null -ne $item) {
    if (($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) {
        throw 'Links/reparse points are refused; nothing transferred.'
    }
    if ($item -is [IO.FileInfo]) { $item = $item.Directory } else { $item = $item.Parent }
}
# No verified upload Issuer ID exists in the checked Shelf records/server metadata.
# Key ID is obtained automatically from the original filename, never retyped.
$issuerId = [Microsoft.VisualBasic.Interaction]::InputBox(
    'Paste only the existing Issuer ID saved for Codemagic shelf_app_store_connect (Team Settings > Integrations > Developer Portal). Do not generate a key.',
    'Existing Shelf upload key: Issuer ID')
$issuerId = $issuerId.Trim()
if ($issuerId -notmatch '^[0-9a-fA-F]{8}(-[0-9a-fA-F]{4}){3}-[0-9a-fA-F]{12}$') {
    throw 'Missing or invalid Issuer ID; nothing transferred.'
}
$stream = [IO.File]::Open($source, 'Open', 'Read', 'Read')
try {
    if ($stream.Length -lt 1 -or $stream.Length -gt 16384) { throw 'Unexpected file size; nothing transferred.' }
    $sha = [Security.Cryptography.SHA256]::Create()
    try { $hash = ([BitConverter]::ToString($sha.ComputeHash($stream))).Replace('-', '').ToLowerInvariant() }
    finally { $sha.Dispose() }
    $stream.Position = 0
    $metadata = @{ key_id = $keyId; issuer_id = $issuerId; size = $stream.Length; sha256 = $hash } | ConvertTo-Json -Compress
    $header = [Text.Encoding]::UTF8.GetBytes($metadata)
    # Only public receiver code goes in the command; identifiers and key use binary stdin.
    $receiver = @'
import os, sys, stat, pwd, re, json, hashlib, struct
ACCOUNT = 'shelf'
ROOT = '/home/shelf'
opened, created, directory = [], [], None

def exact(n):
    data = bytearray()
    while len(data) < n:
        part = sys.stdin.buffer.read(n - len(data))
        if not part:
            raise ValueError('Incomplete input')
        data.extend(part)
    return bytes(data)

try:
    length = struct.unpack('<I', exact(4))[0]
    assert 0 < length <= 4096
    header = json.loads(exact(length))
    assert set(header) == {'key_id', 'issuer_id', 'size', 'sha256'}
    key_id, issuer = header['key_id'], header['issuer_id']
    assert isinstance(key_id, str) and re.fullmatch(r'[A-Z0-9]{10}', key_id)
    assert key_id != '2VQ43945YB'
    assert isinstance(issuer, str) and re.fullmatch(r'[0-9a-fA-F]{8}(-[0-9a-fA-F]{4}){3}-[0-9a-fA-F]{12}', issuer)
    size, expected = header['size'], header['sha256']
    assert type(size) is int and 0 < size <= 16384
    assert isinstance(expected, str) and re.fullmatch(r'[0-9a-f]{64}', expected)
    account = pwd.getpwnam(ACCOUNT)
    flags = os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW
    directory = os.open('/', flags)
    opened.append(directory)
    # Open each component relative to an already open directory; never follow a symlink.
    for component in ROOT.strip('/').split('/') + ['secrets', 'apple']:
        directory = os.open(component, flags, dir_fd=directory)
        opened.append(directory)
        if component in ('secrets', 'apple'):
            s = os.fstat(directory)
            assert s.st_uid == account.pw_uid and s.st_gid == account.pw_gid
            assert stat.S_IMODE(s.st_mode) == 0o700
    names = ('shelf-asc-inspection.p8', 'shelf-asc-inspection.json')
    for name in names:
        try:
            os.stat(name, dir_fd=directory, follow_symlinks=False)
        except FileNotFoundError:
            continue
        raise FileExistsError('Destination exists')
    os.umask(0o077)
    for name in names:
        fd = os.open(name, os.O_RDWR | os.O_CREAT | os.O_EXCL | os.O_NOFOLLOW, 0o600, dir_fd=directory)
        created.append((name, fd, os.fstat(fd).st_ino))
        os.fchown(fd, account.pw_uid, account.pw_gid)
    # Binary writes must handle short writes as well as incomplete input.
    def write_all(fd, data):
        data = memoryview(data)
        while data:
            n = os.write(fd, data)
            assert n > 0
            data = data[n:]
    key_fd = created[0][1]
    remaining = size
    while remaining:
        data = exact(min(8192, remaining))
        write_all(key_fd, data)
        remaining -= len(data)
    assert not sys.stdin.buffer.read(1)
    metadata = (json.dumps({'key_id': key_id, 'issuer_id': issuer}, sort_keys=True) + '\n').encode()
    write_all(created[1][1], metadata)
    for name, fd, inode in created:
        os.fsync(fd)
        os.lseek(fd, 0, os.SEEK_SET)
        saved = bytearray()
        while True:
            data = os.read(fd, 8192)
            if not data:
                break
            saved.extend(data)
        if name.endswith('.p8'):
            assert len(saved) == size and hashlib.sha256(saved).hexdigest() == expected
        else:
            assert bytes(saved) == metadata
        s = os.fstat(fd)
        linked = os.stat(name, dir_fd=directory, follow_symlinks=False)
        assert stat.S_ISREG(linked.st_mode) and linked.st_ino == inode
        assert s.st_uid == account.pw_uid and s.st_gid == account.pw_gid
        assert stat.S_IMODE(s.st_mode) == 0o600 and s.st_nlink == 1
    os.fsync(directory)
except BaseException:
    # Remove only files created by this attempt, and only if the same inode is still linked.
    for name, fd, inode in created:
        try:
            s = os.stat(name, dir_fd=directory, follow_symlinks=False)
            if stat.S_ISREG(s.st_mode) and s.st_ino == inode:
                os.unlink(name, dir_fd=directory)
        except OSError:
            pass
    sys.exit('Transfer refused or failed. Existing files preserved; inspect destinations before retry. No credentials printed.')
finally:
    for name, fd, inode in created:
        os.close(fd)
    for fd in reversed(opened):
        os.close(fd)
print('Transfer verified: key and identifiers stored privately; shelf:shelf; mode 600. No API call made.')
'@
    $encoded = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($receiver))
    $remote = "python3 -B -c 'import base64;exec(base64.b64decode(`"$encoded`"))'"
    $start = New-Object Diagnostics.ProcessStartInfo
    $start.FileName = $ssh
    $remoteArgument = '"' + $remote.Replace('"', '\"') + '"'
    $start.Arguments = '-T -o StrictHostKeyChecking=yes root@157.250.199.106 ' + $remoteArgument
    $start.UseShellExecute = $false
    $start.RedirectStandardInput = $true
    $process = New-Object Diagnostics.Process
    $process.StartInfo = $start
    try {
        [void]$process.Start()
        try {
            # Explicit little-endian length; no PowerShell text pipeline touches key bytes.
            $length = [BitConverter]::GetBytes([uint32]$header.Length)
            if (-not [BitConverter]::IsLittleEndian) { [Array]::Reverse($length) }
            $process.StandardInput.BaseStream.Write($length, 0, $length.Length)
            $process.StandardInput.BaseStream.Write($header, 0, $header.Length)
            $stream.CopyTo($process.StandardInput.BaseStream)
        }
        finally { $process.StandardInput.Close() }
        $process.WaitForExit()
        if ($process.ExitCode -ne 0) { throw 'SSH transfer failed; do not overwrite existing destinations.' }
    }
    finally { $process.Dispose() }
}
finally { $stream.Dispose() }
