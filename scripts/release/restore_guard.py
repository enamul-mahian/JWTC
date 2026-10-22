"""Private backup restore guards. No framework boot or network calls."""
import os
import re
import shutil
import sys
import tarfile
from pathlib import Path, PurePosixPath


def extract(archive, destination, max_bytes):
    root = Path(destination)
    if not root.is_dir() or any(root.iterdir()) or root.is_symlink():
        raise ValueError('Destination must be a new empty directory')
    seen = set()
    total = 0
    with tarfile.open(archive, 'r|gz') as source:
        for member in source:
            name = member.name
            while name.startswith('./'):
                name = name[2:]
            if name in ('', '.') and member.isdir():
                continue
            parts = PurePosixPath(name).parts
            if (not name or name.startswith('/') or '\\' in name or
                    any(part in ('..', '.') for part in parts) or
                    not (member.isdir() or member.isreg()) or member.issparse()):
                raise ValueError('Unsafe archive member')
            normalized = '/'.join(parts)
            if normalized in seen:
                raise ValueError('Duplicate archive member')
            seen.add(normalized)
            target = root.joinpath(*parts)
            target.parent.mkdir(parents=True, exist_ok=True, mode=0o700)
            if member.isdir():
                target.mkdir(exist_ok=True, mode=0o700)
            else:
                total += member.size
                if member.size < 0 or total > int(max_bytes):
                    raise ValueError('Archive size limit exceeded')
                with source.extractfile(member) as incoming, target.open('xb') as outgoing:
                    shutil.copyfileobj(incoming, outgoing)
                os.chmod(target, 0o600)
    return total


def sql_tokens(text):
    # Keep executable MySQL comments, discard ordinary comments. Quoted user
    # values/identifiers are consumed before comment recognition.
    index = 0
    while index < len(text):
        ch = text[index]
        if ch.isspace():
            index += 1
        elif ch in "'\"`":
            quote, start = ch, index
            index += 1
            value = ''
            while index < len(text):
                ch = text[index]
                if ch == '\\':
                    index += 2
                elif ch == quote:
                    if index + 1 < len(text) and text[index + 1] == quote:
                        value += quote
                        index += 2
                    else:
                        index += 1
                        break
                else:
                    value += ch if quote == '`' else ''
                    index += 1
            else:
                raise ValueError('Unterminated SQL quote')
            yield ('identifier', value) if quote == '`' else ('value', '')
        elif text.startswith('--', index) and (index + 2 == len(text) or text[index + 2].isspace()):
            end = text.find('\n', index)
            index = len(text) if end < 0 else end + 1
        elif ch == '#':
            end = text.find('\n', index)
            index = len(text) if end < 0 else end + 1
        elif text.startswith('/*', index):
            end = text.find('*/', index + 2)
            if end < 0:
                raise ValueError('Unterminated SQL comment')
            if text.startswith('/*!', index):
                body = re.sub(r'^\d+\s*', '', text[index + 3:end])
                yield from sql_tokens(body)
            index = end + 2
        elif ch.isdigit():
            match = re.match(r'[0-9]+(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?', text[index:])
            yield ('value', '')
            index += len(match[0])
        elif ch.isalpha() or ch in '_$':
            match = re.match(r'[A-Za-z_$][A-Za-z0-9_$]*', text[index:])
            if not match:
                raise ValueError('Unsupported SQL token')
            yield ('word', match[0].upper())
            index += len(match[0])
        else:
            yield ('symbol', ch)
            index += 1


def validate_statement(tokens):
    if not tokens:
        return
    words = [value for kind, value in tokens if kind == 'word']
    start = words[:2]
    if any(kind == 'symbol' and value == '.' for kind, value in tokens):
        raise ValueError('Qualified SQL references refused')
    if start[:1] == ['SET']:
        if any(word in ('GLOBAL', 'PERSIST', 'PASSWORD', 'ROLE', 'SQL_LOG_BIN') for word in words):
            raise ValueError('Unsafe SET statement')
        # MySQL dump session state only; no stored code or cross-schema writes.
        if any(kind == 'word' and value in ('SELECT', 'LOAD_FILE', 'INTO', 'OUTFILE', 'EVENT_SCHEDULER') for kind, value in tokens):
            raise ValueError('Unsafe SET expression')
    elif start == ['CREATE', 'TABLE']:
        if len(tokens) < 4 or tokens[2][0] != 'identifier':
            raise ValueError('Unexpected CREATE TABLE')
    elif start == ['INSERT', 'INTO']:
        if len(tokens) < 4 or tokens[2][0] != 'identifier' or 'VALUES' not in words:
            raise ValueError('Unexpected INSERT')
        if any(word in ('SELECT', 'OUTFILE', 'LOAD_FILE') for word in words):
            raise ValueError('Unsafe INSERT expression')
    elif start == ['LOCK', 'TABLES']:
        if len(tokens) < 3 or tokens[2][0] != 'identifier':
            raise ValueError('Unexpected table lock')
    elif start == ['UNLOCK', 'TABLES']:
        if len(tokens) != 2:
            raise ValueError('Unexpected unlock')
    elif start == ['ALTER', 'TABLE']:
        if len(tokens) != 5 or tokens[2][0] != 'identifier' or words[2:] not in (['DISABLE', 'KEYS'], ['ENABLE', 'KEYS']):
            raise ValueError('Only dump key toggles allowed')
    else:
        raise ValueError('Unexpected SQL statement type')


def validate_sql(path):
    if Path(path).stat().st_size > 256 * 1024 * 1024:
        raise ValueError('Dump requires streaming review')
    text = Path(path).read_text(encoding='utf-8')
    statement = []
    count = 0
    for token in sql_tokens(text):
        if token == ('symbol', ';'):
            validate_statement(statement)
            count += bool(statement)
            statement = []
        else:
            statement.append(token)
    validate_statement(statement)
    if not count:
        raise ValueError('Empty SQL dump')
    return count


if __name__ == '__main__':
    try:
        if sys.argv[1] == 'extract':
            extract(sys.argv[2], sys.argv[3], sys.argv[4])
        elif sys.argv[1] == 'sql':
            validate_sql(sys.argv[2])
        else:
            raise ValueError('Unknown operation')
    except Exception:
        print('Restore guard failed; private details suppressed', file=sys.stderr)
        sys.exit(1)
