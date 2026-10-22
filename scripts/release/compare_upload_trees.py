#!/usr/bin/env python3
"""Read-only byte-for-byte comparison of live and staging upload folders.

Never prints source filenames, contents, secrets or personal identifiers.
Run only by an authorized operator; do not point to publicly exposed reports.
Symlinks are rejected rather than followed into other data stores.
"""
from __future__ import annotations

import argparse
import hashlib
import os
import pathlib
import sys


class UnsafeTreeError(ValueError):
    pass


def safe_root(path: pathlib.Path) -> pathlib.Path:
    if path.is_symlink() or not path.is_dir():
        raise UnsafeTreeError("Root missing, symlinked, or not a directory")
    return path.resolve(strict=True)


def inspect_path(root: pathlib.Path, relative: str) -> pathlib.Path:
    parts = pathlib.PurePosixPath(relative).parts
    if not parts or relative.startswith("/") or "\\" in relative or ".." in parts:
        raise UnsafeTreeError("Unsafe relative path")
    current = root
    for part in parts:
        if part in (".", ""):
            raise UnsafeTreeError("Unsafe relative path segment")
        current = current / part
        if current.is_symlink():
            raise UnsafeTreeError("Tree contains a symlink")
    directory = current.resolve(strict=True)
    if not directory.is_dir() or not directory.is_relative_to(root):
        raise UnsafeTreeError("Directory not contained in designated root")
    return directory


def hash_file(path: pathlib.Path) -> str:
    sha = hashlib.sha256()
    with path.open("rb") as data:
        while chunk := data.read(4 * 1024 * 1024):
            sha.update(chunk)
    return sha.hexdigest()


def inventory(root: pathlib.Path, relative: str) -> dict[str, tuple[int, str]]:
    folder = inspect_path(root, relative)
    files: dict[str, tuple[int, str]] = {}
    for directory, dirs, names in os.walk(folder, followlinks=False):
        parent = pathlib.Path(directory)
        for entry in dirs:
            if (parent / entry).is_symlink():
                raise UnsafeTreeError("Folder includes linked directory")
        for name in names:
            candidate = parent / name
            if candidate.is_symlink() or not candidate.is_file():
                raise UnsafeTreeError("Folder includes linked or non-regular file")
            token = candidate.relative_to(folder).as_posix()
            files[token] = (candidate.stat().st_size, hash_file(candidate))
    return files


def compare(source: pathlib.Path, restored: pathlib.Path,
            paths: list[str]) -> tuple[bool, dict[str, int]]:
    left = safe_root(source)
    right = safe_root(restored)
    if left == right:
        raise UnsafeTreeError("Source and destination are the same directory")
    totals = {"source_files": 0, "restored_files": 0,
              "missing": 0, "modified": 0, "added": 0}

    for path in paths:
        baseline = inventory(left, path)
        candidate = inventory(right, path)
        totals["source_files"] += len(baseline)
        totals["restored_files"] += len(candidate)
        totals["missing"] += sum(key not in candidate for key in baseline)
        totals["modified"] += sum(
            baseline[key] != candidate[key] for key in baseline if key in candidate
        )
        totals["added"] += sum(key not in baseline for key in candidate)

    return totals["missing"] == 0 and totals["modified"] == 0, totals


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Read-only full-hash upload folder comparison; no filenames printed")
    parser.add_argument("--source-root", type=pathlib.Path, required=True)
    parser.add_argument("--restored-root", type=pathlib.Path, required=True)
    parser.add_argument("--path", action="append", required=True,
                        help="Relative folder to compare; repeat for every upload root")
    args = parser.parse_args()

    try:
        safe, counts = compare(args.source_root, args.restored_root, args.path)
    except (UnsafeTreeError, OSError, ValueError) as error:
        print(f"BLOCKED: {type(error).__name__}; no data copied or changed.",
              file=sys.stderr)
        return 2

    print(
        "Files checked: source={source_files}, restored={restored_files}, "
        "missing={missing}, modified={modified}, extra={added}".format(**counts)
    )
    if not safe:
        print("FAIL: Files missing or different; stop the release.", file=sys.stderr)
        return 1
    print("PASS: Every inventoried source upload has an identical restored copy.")
    print("NOTE: Does not check database, ACLs, newly written files or backup restore.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
