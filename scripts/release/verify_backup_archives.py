#!/usr/bin/env python3
"""Read-only backup archive integrity inspection for JWTC release evidence.

This is not a restore test. It does not extract or alter database/files,
and never prints SQL contents, archive members or credentials.
"""
from __future__ import annotations

import argparse
import gzip
import hashlib
import pathlib
import sys
import tarfile
import zipfile


def checksum(file: pathlib.Path) -> str:
    digest = hashlib.sha256()
    with file.open("rb") as stream:
        while part := stream.read(4 * 1024 * 1024):
            digest.update(part)
    return digest.hexdigest()


def verify_sql(file: pathlib.Path) -> None:
    if file.name.lower().endswith(".sql.gz"):
        with gzip.open(file, "rb") as stream:
            snippet = stream.read(256 * 1024)
            while stream.read(4 * 1024 * 1024):
                pass  # verify gzip stream and CRC without exposing SQL
    elif file.suffix.lower() == ".sql":
        with file.open("rb") as stream:
            snippet = stream.read(256 * 1024)
    else:
        raise ValueError("DB backup must have .sql or .sql.gz extension")
    if not snippet or (b"CREATE TABLE" not in snippet.upper()
                       and b"INSERT INTO" not in snippet.upper()):
        raise ValueError("SQL backup header contains no recognizable table data")


def valid_archive_entry(name: str) -> bool:
    path = pathlib.PurePosixPath(name.replace("\\", "/"))
    return bool(name) and not path.is_absolute() and ".." not in path.parts


def verify_uploads(file: pathlib.Path) -> None:
    lower = file.name.lower()
    if lower.endswith(".tar.gz") or lower.endswith(".tgz") or lower.endswith(".tar"):
        with tarfile.open(file, mode="r:*") as archive:
            count = 0
            for entry in archive:
                if not valid_archive_entry(entry.name):
                    raise ValueError("Unsafe archive member path")
                if entry.issym() or entry.islnk():
                    raise ValueError("Archive contains links; inspect before restore")
                if entry.isfile():
                    count += 1
            if not count:
                raise ValueError("Files archive is empty")
    elif lower.endswith(".zip"):
        with zipfile.ZipFile(file) as archive:
            if not any(not info.is_dir() for info in archive.infolist()):
                raise ValueError("Files archive is empty")
            for info in archive.infolist():
                if not valid_archive_entry(info.filename):
                    raise ValueError("Unsafe archive member path")
                if info.external_attr >> 16 & 0o170000 == 0o120000:
                    raise ValueError("Archive contains a symbolic link")
            if archive.testzip() is not None:
                raise ValueError("ZIP archive has a CRC error")
    else:
        raise ValueError("Files backup must be .tar, .tar.gz, .tgz or .zip")


def check(file: pathlib.Path, kind: str) -> str:
    if not file.is_file() or file.stat().st_size < 512:
        raise ValueError("Backup file is absent or suspiciously small")
    if kind == "db":
        verify_sql(file)
    else:
        verify_uploads(file)
    return (
        f"{kind.upper()}: verified archive readability, "
        f"size={file.stat().st_size} bytes, SHA256={checksum(file)}"
    )


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Inspect SQL and upload archives WITHOUT extracting or restoring")
    parser.add_argument("--database", type=pathlib.Path, required=True)
    parser.add_argument("--files", type=pathlib.Path, action="append", required=True,
                        help="Repeat for every private/public upload archive")
    args = parser.parse_args()
    try:
        print(check(args.database, "db"))
        for part in args.files:
            print(check(part, "files"))
    except (ValueError, OSError, EOFError, gzip.BadGzipFile, tarfile.TarError,
            zipfile.BadZipFile, zipfile.LargeZipFile) as error:
        # Never log paths, archive member names or sensitive content.
        print(f"BACKUP INTEGRITY CHECK FAILED: {type(error).__name__}", file=sys.stderr)
        return 2
    print("Archive integrity only. Restore DB+uploads on isolated staging before release.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
