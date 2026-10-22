#!/usr/bin/env python3
"""Read-only metadata audit of a private phpMyAdmin dump versus migrations.

Never commits, copies, logs or returns SQL data rows; checks DDL names only.
Does NOT prove compatibility, restoration or production data completeness.
"""
from __future__ import annotations

import argparse
from dataclasses import dataclass
from pathlib import Path
import re
import sys

CREATE = re.compile(
    r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?\x60([A-Za-z0-9_]+)\x60\s*"
    r"\((.*?)\)\s*ENGINE\s*=", re.I | re.S
)
COLUMN = re.compile(r"(?:^|,)\s*\x60([A-Za-z0-9_]+)\x60\s+", re.M)
MIGRATIONS_INSERT = re.compile(
    r"INSERT\s+INTO\s+\x60migrations\x60\s*\(([^)]*)\)\s*VALUES\s*(.*?);",
    re.I | re.S
)
MIGRATION_TUPLE = re.compile(
    r"\(\s*\d+\s*,\s*'([A-Za-z0-9_]+)'\s*,\s*\d+\s*\)"
)
CREATE_IN_UP = re.compile(
    r"Schema\s*::\s*create\s*\(\s*['\"]([A-Za-z0-9_]+)['\"]", re.I
)
UP = re.compile(r"\bfunction\s+up\s*\(", re.I)
DOWN = re.compile(r"\bfunction\s+down\s*\(", re.I)
NEW_START = "2026_10_22"

REQUIRED = {
    "users": {"id", "email", "user_type", "job_category_id", "deleted_at"},
    "jobs": {"id", "user_id", "company_id", "country_id", "title",
             "is_publish", "application_deadline", "deleted_at"},
    "applications": {"id", "user_id", "employer_id", "job_id",
                     "status", "date_applied", "expected_salary"},
    "companies": {"id", "user_id", "company_name"},
    "unique_codes": {"id", "user_id", "code", "is_used"},
    "payment_credentials": {"id", "name", "value"},
    "uploaded_cvs": {"id", "user_id", "file"},
    "jobseeker_uploads": {"id", "user_id", "file"},
    "jobseeker_certificates": {"id", "user_id", "file"},
    "migrations": {"id", "migration", "batch"},
}


@dataclass
class Audit:
    tables: dict[str, set[str]]
    applied: set[str]
    historical: set[str]
    additions: set[str]
    new_tables: set[str]
    blockers: list[str]


def inspect(sql: str, migrations: dict[str, str],
            required: dict[str, set[str]] | None = None) -> Audit:
    required = REQUIRED if required is None else required
    tables = {name: set(COLUMN.findall(ddl))
              for name, ddl in CREATE.findall(sql)}
    applied: set[str] = set()
    for cols, data in MIGRATIONS_INSERT.findall(sql):
        if re.findall(r"\x60([^\x60]+)\x60", cols) == ["id", "migration", "batch"]:
            applied.update(MIGRATION_TUPLE.findall(data))

    historical = {name for name in migrations if name[:10] < NEW_START}
    additions = {name for name in migrations if name[:10] >= NEW_START}
    blockers: list[str] = []

    if not tables or not applied:
        blockers.append("No recognized DDL or migration records.")
    for table, fields in sorted(required.items()):
        if table not in tables:
            blockers.append("Missing legacy table: " + table)
        else:
            for name in sorted(fields - tables[table]):
                blockers.append("Missing legacy column: " + table + "." + name)

    for name in sorted(historical - applied):
        blockers.append("Historical migration absent from SQL history: " + name)
    for name in sorted(applied - historical - additions):
        blockers.append("SQL migration absent from source repository: " + name)

    new_tables: set[str] = set()
    for name in sorted(additions):
        code = migrations[name]
        start, end = UP.search(code), DOWN.search(code)
        if not start or not end or end.start() <= start.start():
            blockers.append("Uninspectable migration up(): " + name)
            continue
        for table in CREATE_IN_UP.findall(code[start.end():end.start()]):
            if table in tables:
                blockers.append("New migration re-creates old table: " + table)
            new_tables.add(table)

    return Audit(tables, applied, historical, additions, new_tables, blockers)


def main() -> int:
    parser = argparse.ArgumentParser(description="Private metadata-only SQL dump audit")
    parser.add_argument("--sql", type=Path, required=True)
    parser.add_argument("--migrations", type=Path,
                        default=Path("database/migrations"))
    args = parser.parse_args()
    if not args.sql.is_file() or not args.migrations.is_dir():
        print("BLOCKED: SQL or migration directory missing.")
        return 2
    sql = args.sql.read_text(encoding="utf-8-sig", errors="replace")
    code = {p.stem: p.read_text(encoding="utf-8-sig", errors="replace")
            for p in args.migrations.glob("*.php")}
    report = inspect(sql, code)
    print("Legacy table count:", len(report.tables))
    print("Applied SQL migrations:", len(report.applied))
    print("Historical source migrations:", len(report.historical))
    print("Proposed new migrations:", len(report.additions))
    print("New table names:", len(report.new_tables))
    for problem in report.blockers:
        print("BLOCKER:", problem)
    if report.blockers:
        print("BLOCKED: Review schema and migration differences.")
        return 1
    print("PASS: Critical columns, migration history and new-table collisions.")
    print("NOT a MySQL import, a verified restore or a guarantee of zero data loss.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
