"""Build an application candidate from tracked runtime code and clean CI outputs.

This is NOT a full site backup or approval to deploy. Existing private/public
uploads and omitted legacy assets require separate staging reconciliation.
"""
import argparse
import hashlib
import json
import re
import subprocess
import tarfile
from pathlib import Path, PurePosixPath

RUNTIME_DIRS = {'app', 'config', 'database', 'resources', 'routes', 'public'}
RUNTIME_FILES = {'artisan', 'composer.json', 'composer.lock', 'bootstrap/app.php'}
PRIVATE_PUBLIC_DIRS = {
    'storage', 'uploads', 'documents', 'passports', 'cv', 'cvs', 'resumes',
    'files', 'attachments', 'media', 'images',
}
REQUIRED = {
    'artisan', 'composer.json', 'composer.lock', 'bootstrap/app.php',
    'public/index.php', 'public/build/manifest.json', 'vendor/autoload.php',
}


def private_path(name):
    p = PurePosixPath(name)
    if p.is_absolute() or '..' in p.parts or '\\' in name:
        return True
    if any(part.startswith('.env') for part in p.parts):
        return True
    if p.suffix.lower() in {'.sql', '.sqlite', '.zip', '.log', '.bak', '.key', '.pem'}:
        return True
    if any(part in {'node_modules', '.git', 'tests'} for part in p.parts):
        return True
    if name.startswith(('storage/', 'bootstrap/cache/')):
        return True
    if len(p.parts) > 1 and p.parts[0] == 'public' and p.parts[1] in PRIVATE_PUBLIC_DIRS:
        return True
    return name == 'public/hot'


def tracked_runtime(name):
    p = PurePosixPath(name)
    return (not private_path(name) and (
        name in RUNTIME_FILES or (p.parts and p.parts[0] in RUNTIME_DIRS)
    ))


def build(root, output, commit, tracked):
    root = Path(root).resolve()
    output = Path(output).resolve()
    if not re.fullmatch(r'[0-9a-f]{40}', commit):
        raise ValueError('A full Git commit SHA is required')
    if output == root or root in output.parents:
        raise ValueError('Output must be outside application source')
    names = {name for name in tracked if tracked_runtime(name)}
    for directory in ('vendor', 'public/build'):
        for path in (root / directory).rglob('*'):
            if path.is_symlink():
                # Composer bin links are unnecessary in the web runtime.
                if directory == 'vendor' and (root / 'vendor/bin') in path.parents:
                    continue
                raise ValueError('Symlink in build output')
            if path.is_file():
                name = path.relative_to(root).as_posix()
                if not private_path(name):
                    names.add(name)
    if not REQUIRED.issubset(names):
        raise ValueError('Candidate lacks essential runtime files')
    production_dependencies = json.loads((root / 'vendor/composer/installed.json').read_text())
    if production_dependencies.get('dev') is not False:
        raise ValueError('Vendor must be installed with --no-dev')
    manifest = json.loads((root / 'public/build/manifest.json').read_text())
    if not isinstance(manifest, dict) or not manifest:
        raise ValueError('Vite manifest is empty')
    for entry in manifest.values():
        for asset in [entry.get('file', ''), *entry.get('css', []), *entry.get('assets', [])]:
            if not asset or private_path(asset) or 'public/build/' + asset not in names:
                raise ValueError('Vite manifest references missing or unsafe asset')
    records = []
    for name in sorted(names):
        path = root / name
        if path.is_symlink() or not path.is_file() or root not in path.resolve().parents:
            raise ValueError('Unsafe runtime file')
        records.append({'path': name, 'sha256': hashlib.sha256(path.read_bytes()).hexdigest()})
    output.mkdir(parents=True, exist_ok=True)
    package = output / ('jwtc-' + commit + '.tar.gz')
    with tarfile.open(package, 'w:gz') as archive:
        for record in records:
            archive.add(root / record['path'], arcname=record['path'], recursive=False)
        for name in ('bootstrap/cache', 'storage/app', 'storage/framework/cache/data',
                     'storage/framework/sessions', 'storage/framework/views', 'storage/logs'):
            info = tarfile.TarInfo(name)
            info.type = tarfile.DIRTYPE
            info.mode = 0o750
            archive.addfile(info)
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    result = {
        'commit': commit, 'package': package.name, 'sha256': digest,
        'production_ready': False,
        'scope': 'CI runtime candidate; excludes environment, all uploads and legacy image assets',
        'files': records,
    }
    (output / 'release-manifest.json').write_text(json.dumps(result, indent=2) + '\n')
    (output / 'SHA256SUMS').write_text(digest + '  ' + package.name + '\n')
    return result


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--root', required=True)
    parser.add_argument('--output', required=True)
    parser.add_argument('--commit', required=True)
    args = parser.parse_args()
    tracked = subprocess.check_output(['git', 'ls-files', '-z'], cwd=args.root).decode().split('\0')
    result = build(args.root, args.output, args.commit, tracked)
    print('PASS: application candidate packaged; files=' + str(len(result['files'])))
    print('INFO: no production backup, staging acceptance or deployment performed')
