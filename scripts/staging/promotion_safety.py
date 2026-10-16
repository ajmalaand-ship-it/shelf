"""Narrow historical refresh proof and bounded operator locks. Never prints row data."""
from datetime import datetime, timezone
import hashlib
import json
import os
from pathlib import Path
import re
import signal
import stat
import subprocess
import time

SOURCE = '1d3dfa5abc1bb35097ada6f2a69fb003c2e57b19'
PREVIOUS = 'c8c266221d972d409ffa947ca87397293c69c004'
CHECKPOINT = Path('/home/shelf/backups/shelf/20261016-004143/apple-session-rollout-checkpoint')
CHECKPOINT_SHA256 = 'a9ff66431e3a031a1c7e960594a46e8b2358855b90b757328c044a5dc0ae425d'
META = ('checked_at', 'created_at', 'updated_at')
ENT_COLUMNS = ('id', 'reader_id', 'collection_id', 'active', *META)
ACTIVE = None


def require(ok, message):
    if not ok:
        raise RuntimeError(message)


def epoch(value):
    """Dump TIMESTAMP(0) is UTC. DATETIME values are never sent through this function."""
    require(isinstance(value, str) and re.fullmatch(r'\d{4}-\d\d-\d\d \d\d:\d\d:\d\d', value), 'Unverified timestamp precision/format.')
    return int(datetime.strptime(value, '%Y-%m-%d %H:%M:%S').replace(tzinfo=timezone.utc).timestamp())


def sql_values(sql, position):
    """Read SQL literals in VALUES only; accepts multiline and extended inserts."""
    escape = {'0':'\0', 'n':'\n', 'r':'\r', 't':'\t', 'Z':'\x1a'}
    rows = []
    while position < len(sql):
        while sql[position].isspace() or sql[position] == ',':
            position += 1
        if sql[position] == ';':
            return rows
        require(sql[position] == '(', 'Unrecognized backup tuple.')
        position += 1
        row = []
        while True:
            while sql[position].isspace():
                position += 1
            if sql[position] == "'":
                position += 1
                value = []
                while True:
                    char = sql[position]
                    position += 1
                    if char == '\\':
                        char = sql[position]
                        position += 1
                        value.append(escape.get(char, char))
                    elif char == "'":
                        if sql[position] == "'":
                            value.append("'")
                            position += 1
                        else:
                            break
                    else:
                        value.append(char)
                value = ''.join(value)
            else:
                begin = position
                while sql[position] not in ',)':
                    position += 1
                value = sql[begin:position].strip()
                value = None if value == 'NULL' else value
            row.append(value)
            while sql[position].isspace():
                position += 1
            if sql[position] == ')':
                position += 1
                break
            require(sql[position] == ',', 'Malformed backup field separator.')
            position += 1
        rows.append(row)
    raise RuntimeError('Incomplete backup INSERT.')


def dump_rows(sql, table, columns):
    """Parse only fixed numeric/audit tables; never execute backup SQL."""
    rows = []
    for match in re.finditer(r'^INSERT INTO `'+re.escape(table)+r'`(?:\s*\(([^;]*?)\))?\s+VALUES\s*', sql, re.M):
        zones = re.findall(r'SET TIME_ZONE\s*=\s*([^;*\n]+)', sql[:match.start()])
        require(zones and all(z.strip() == "'+00:00'" for z in zones), 'SQL dump timezone is not proven UTC.')
        names = re.findall(r'`([^`]+)`', match[1]) if match[1] else list(columns)
        require(tuple(names) == tuple(columns), 'Unexpected backup column order/schema.')
        for values in sql_values(sql, match.end()):
            require(len(values) == len(columns), 'Incomplete backup row.')
            row = {}
            for key, value in zip(columns, values):
                if value is None:
                    row[key] = None
                elif key in (*META, 'started_at', 'finished_at'):
                    row[key] = epoch(value)
                elif key in ('status', 'error_code'):
                    row[key] = value
                else:
                    require(re.fullmatch(r'\d+', value) is not None, 'Unrecognized backup numeric field.')
                    row[key] = int(value)
            rows.append(row)
    require(len({r['id'] for r in rows}) == len(rows), 'Duplicate backup row ids.')
    return rows


def raw_entitlement_hash(row, offset):
    raw = dict(row)
    for key in META:
        raw[key] = None if raw[key] is None else datetime.fromtimestamp(raw[key]+offset, timezone.utc).strftime('%Y-%m-%d %H:%M:%S')
    return hashlib.sha256(json.dumps([raw], separators=(',', ':'), ensure_ascii=True).encode()).hexdigest()


AUDIT_COLUMNS = ('id', 'status', 'http_status', 'pages', 'seen', 'refunded', 'duplicate',
                 'unmatched', 'conflict', 'error_code', 'started_at', 'finished_at', 'created_at')


def private_bytes(path, secret=True):
    path = Path(path)
    s = path.lstat()
    require(path.resolve() == path and stat.S_ISREG(s.st_mode) and s.st_uid == os.getuid()
            and (stat.S_IMODE(s.st_mode) == 0o600 if secret else not s.st_mode & 0o022), 'Unsafe preservation evidence file.')
    return path.read_bytes()


def load_checkpoint(path, source, previous):
    path = Path(path)
    require(path == CHECKPOINT and path.resolve() == path and source == SOURCE and previous == PREVIOUS,
            'Historical allowance is restricted to the reviewed checkpoint/source.')
    original = private_bytes(path/'verification.json')
    require(hashlib.sha256(original).hexdigest() == CHECKPOINT_SHA256, 'Original preservation checkpoint changed; no rebaselining.')
    record = json.loads(original)
    require(record['source'] == source and record['previous'] == previous
            and record['configuration_checkpoint_verified'] is True, 'Preservation provenance mismatch.')
    require(Path(record['backup']) == path.parent, 'Unexpected backup location.')
    manifest = json.loads(private_bytes(path.parent/'manifest.json'))
    require(manifest.get('system') == 'Shelf', 'Unexpected backup manifest system.')
    sql = private_bytes(path.parent/'database.sql')
    require(hashlib.sha256(sql).hexdigest() == manifest['database_sha256'], 'SQL backup checksum mismatch.')
    for filename, expected in record['files'].items():
        require(hashlib.sha256(private_bytes(Path(filename), secret=not filename.endswith('/public/.htaccess'))).hexdigest() == expected,
                'Protected configuration or handler changed.')
    text = sql.decode('utf-8')
    return record, dump_rows(text, 'book_entitlements', ENT_COLUMNS), dump_rows(text, 'play_refund_runs', AUDIT_COLUMNS)


def compare_history(record, old_entitlements, old_runs, current_history, live):
    """Only reviewed row 5 metadata may vary; all ownership/money fingerprints stay exact."""
    old_history = record['history']
    require(set(old_history) == set(current_history), 'Protected history table set changed.')
    for table in old_history:
        if table != 'book_entitlements':
            require(old_history[table] == current_history[table], 'Protected history changed: '+table)
    # Historical hashes were captured through SYSTEM/AST. Refuse changed session
    # semantics; compare dump/live entitlement and audit TIMESTAMPs using UTC epochs.
    require(live['offset_seconds'] == -14400 and live['precision_zero'] is True
            and live['columns'] == list(ENT_COLUMNS), 'Unverified historical session/schema semantics.')
    require(len(old_entitlements) == 1 and old_entitlements[0]['id'] == 5, 'Allowance only covers reviewed entitlement row 5.')
    require(old_history['book_entitlements']['count'] == current_history['book_entitlements']['count'] == 1,
            'Entitlement row count changed.')
    old = old_entitlements[0]
    require(raw_entitlement_hash(old, -14400) == old_history['book_entitlements']['sha256'],
            'Backup does not reproduce original entitlement checkpoint.')
    rows = live['entitlements']
    require(len(rows) == 1 and set(rows[0]) == set(ENT_COLUMNS), 'Entitlement row/schema set changed.')
    new = rows[0]
    require(raw_entitlement_hash(new, live['offset_seconds']) == current_history['book_entitlements']['sha256'],
            'Live entitlement evidence does not match coherent history.')
    require(all(old[k] == new[k] for k in ENT_COLUMNS if k not in META), 'Entitlement ownership or status changed.')
    current_runs = live['refund_runs']
    require(len({r['id'] for r in current_runs}) == len(current_runs), 'Ambiguous audit ids.')
    require(old_runs and sorted((r for r in current_runs if r['id'] <= max(o['id'] for o in old_runs)), key=lambda r:r['id'])
            == sorted(old_runs, key=lambda r:r['id']), 'Existing refund audit history changed.')
    changed = [k for k in META if old[k] != new[k]]
    if not changed:
        require(old_history['book_entitlements'] == current_history['book_entitlements'], 'Unexpected entitlement serialization drift.')
        return {'entitlement_id': 5, 'changed_columns': [], 'run_id': None}
    require(changed == list(META) and new['checked_at'] is not None
            and len({new[k] for k in META}) == 1
            and all(old[k] is not None and new[k] >= old[k] for k in META), 'Unexplained timestamp change.')
    candidates = [r for r in current_runs if r['id'] > max(o['id'] for o in old_runs)
                  and r['started_at'] <= new['checked_at'] <= r['finished_at']]
    require(len(candidates) == 1, 'Missing/ambiguous matching refund run.')
    run = candidates[0]
    require(run['status'] == 'completed' and run['http_status'] == 200 and run['error_code'] is None
            and run['seen'] == run['duplicate'] == 1 and run['pages'] == 1
            and all(run[k] == 0 for k in ('refunded', 'unmatched', 'conflict')), 'Refund run is not duplicate-only.')
    require(live['link'] == {'refunded_global':1, 'refunded_same_book':1, 'google_events':1, 'valid_play':1},
            'Refund run cannot be uniquely linked to the preserved entitlement.')
    return {'entitlement_id':5, 'changed_columns':changed, 'run_id':run['id'],
            'before_utc_epoch':old['checked_at'], 'after_utc_epoch':new['checked_at']}


class PromotionInterrupted(RuntimeError):
    pass


class PromotionWindow:
    """No cron edits. Kernel locks and independently timed child processes bound suppression."""
    def __init__(self, path, cleanup, seconds=600):
        self.path, self.cleanup = Path(path), cleanup
        self.seconds = seconds
        self.fd = None
        self.maintenance = False
        self.cleaning = False
        self.interrupted = False
        self.cleanup_attempted = False

    def __enter__(self):
        global ACTIVE
        import fcntl
        require(ACTIVE is None and 0 < self.seconds <= 600, 'Invalid/concurrent promotion window.')
        self.fd = os.open(self.path, os.O_RDWR | os.O_NOFOLLOW | os.O_CLOEXEC)
        try:
            s = os.fstat(self.fd)
            require(stat.S_ISREG(s.st_mode) and s.st_uid == os.getuid() and s.st_nlink == 1
                    and not s.st_mode & 0o077, 'Unsafe refund-runner lock.')
            fcntl.flock(self.fd, fcntl.LOCK_EX | fcntl.LOCK_NB)
            self.handlers = {sig:signal.getsignal(sig) for sig in (signal.SIGINT, signal.SIGTERM, signal.SIGHUP, signal.SIGALRM)}
            self.old_timer = signal.getitimer(signal.ITIMER_REAL)
            require(self.old_timer == (0.0, 0.0), 'Existing process timer; refusing to replace it.')
            for sig in self.handlers:
                signal.signal(sig, self.on_signal)
            self.deadline = time.monotonic() + self.seconds
            ACTIVE = self
            signal.setitimer(signal.ITIMER_REAL, self.seconds)
            return self
        except BaseException:
            os.close(self.fd)
            self.fd = None
            raise

    def on_signal(self, sig, frame):
        if not self.cleaning and not self.interrupted:
            self.interrupted = True
            raise PromotionInterrupted('Promotion interrupted or deadline expired; cleanup required.')

    def arm_maintenance(self):
        self.maintenance = True  # before down, so partial failure also requests up

    def restore_maintenance(self, exiting=False):
        if not self.maintenance:
            return
        require(not self.cleanup_attempted, 'Maintenance cleanup failed previously; stop and recover.')
        self.cleanup_attempted = True
        original_deadline = self.deadline
        self.cleaning = True
        signal.setitimer(signal.ITIMER_REAL, 0)
        self.deadline = time.monotonic() + 30
        try:
            self.cleanup()
            self.maintenance = False
        finally:
            self.cleaning = False
            self.deadline = original_deadline
        if not exiting:
            remaining = self.deadline - time.monotonic()
            require(remaining > 0, 'Promotion deadline expired after maintenance cleanup.')
            signal.setitimer(signal.ITIMER_REAL, remaining)

    def __exit__(self, kind, error, trace):
        global ACTIVE
        try:
            try:
                self.restore_maintenance(exiting=True)
            except BaseException as cleanup_error:
                raise RuntimeError('Maintenance cleanup failed; original='+str(kind.__name__ if kind else 'none')+'; stop and recover.') from cleanup_error
        finally:
            signal.setitimer(signal.ITIMER_REAL, 0)
            ACTIVE = None
            os.close(self.fd)  # never delete/replace lock inode, never edit crontab
            self.fd = None
            for sig, handler in self.handlers.items():
                signal.signal(sig, handler)
        return False

    def process(self, args, **kwargs):
        remaining = self.deadline - time.monotonic()
        require(remaining > 0, 'Promotion deadline expired.')
        # timeout outlives an uncatchable parent kill. It inherits this lock, so
        # an orphaned command cannot race polling; timeout kills its process group.
        command = ['/usr/bin/timeout', '--signal=TERM', '--kill-after=5', str(min(120, remaining))+'s', *args]
        child = subprocess.Popen(command, stdin=subprocess.PIPE if kwargs.get('input') is not None else None,
            stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, cwd=kwargs.get('cwd'),
            start_new_session=True, pass_fds=(self.fd,))
        try:
            out, err = child.communicate(kwargs.get('input'), timeout=min(125, remaining+5))
            return subprocess.CompletedProcess(args, child.returncode, out, err)
        except BaseException:
            try:
                os.killpg(child.pid, signal.SIGTERM)
                child.communicate(timeout=5)
            except (ProcessLookupError, subprocess.TimeoutExpired):
                try:
                    os.killpg(child.pid, signal.SIGKILL)
                except ProcessLookupError:
                    pass
                child.communicate()
            raise
