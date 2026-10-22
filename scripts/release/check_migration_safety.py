#!/usr/bin/env python3
"""Fail closed on destructive new migration up() implementations.

Designed for CI review, NOT as proof of a safe production migration.
Existing historical migrations are preserved and deliberately not rewritten.
"""
from __future__ import annotations

import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parents[2]
MIGRATIONS = ROOT / "database" / "migrations"
NEW_MODULE_PATTERN = "2026_10_22_*.php"

UNSAFE_PATTERNS = {
    "table drop": r"Schema\s*::\s*drop(?:IfExists)?\s*\(",
    "table rename": r"Schema\s*::\s*rename\s*\(",
    "column/index drop": r"->\s*drop(?:Column|Foreign|Index|Unique|Primary|SoftDeletes|Timestamps)\s*\(",
    "column rename": r"->\s*renameColumn\s*\(",
    "bulk deletion": r"->\s*(?:delete|forceDelete|truncate)\s*\(",
    "bulk record overwrite": r"->\s*(?:update|upsert|updateOrInsert)\s*\(",
    "raw SQL execution": r"(?:DB|\\\\DB)\s*::\s*(?:statement|unprepared|affectingStatement)\s*\(",
    "destructive artisan commands": r"(?:migrate:fresh|migrate:reset|migrate:refresh|db:wipe|schema:drop)",
    "filesystem delete": r"(?:File|Storage)\s*::\s*(?:delete|deleteDirectory|cleanDirectory)\s*\(",
}

UP_RE = re.compile(r"\bfunction\s+up\s*\([^)]*\)\s*(?::\s*void)?\s*\{", re.I)
DOWN_RE = re.compile(r"\bfunction\s+down\s*\(", re.I)


def isolate_up(contents: str) -> str | None:
    start = UP_RE.search(contents)
    if start is None:
        return None
    # Laravel anonymous migration files put up() before down().
    after = contents[start.end():]
    down = DOWN_RE.search(after)
    return after[:down.start()] if down else None


def violations(contents: str) -> list[str]:
    up = isolate_up(contents)
    if up is None:
        return ["Missing standard up() followed by down() — manual review required"]
    # Remove comments before checking; never automatically change migrations.
    up = re.sub(r"/\*.*?\*/|//[^\n]*|#[^\n]*", "", up, flags=re.S)
    return [name for name, pattern in UNSAFE_PATTERNS.items()
            if re.search(pattern, up, re.I)]


def run() -> int:
    files = sorted(MIGRATIONS.glob(NEW_MODULE_PATTERN))
    if not files:
        print("FAIL: New module migrations absent; guard cannot validate.")
        return 1
    failed = False
    for file in files:
        issues = violations(file.read_text(encoding="utf-8"))
        if issues:
            print(f"FAIL {file.relative_to(ROOT)}: {', '.join(issues)}")
            failed = True
    if failed:
        print("A reviewer must design an additive migration and independently verify restore.")
        return 1
    print(f"PASS: Scanned {len(files)} additive JWTC module migrations (up() only).")
    print("NOTE: Static inspection does NOT replace DB backup or a staging restore test.")
    return 0


if __name__ == "__main__":
    sys.exit(run())
