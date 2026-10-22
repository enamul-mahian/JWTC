"""Synthetic self-tests for immutable data release safety checks."""
from __future__ import annotations

import io
import os
import pathlib
import sys
import tarfile
import tempfile
import unittest

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
import check_migration_safety as migration
import verify_backup_archives as backups


def migration_php(up: str, down: str = "Schema::dropIfExists('module_events');") -> str:
    return (
        "<?php return new class extends Migration { "
        "public function up(): void { " + up + " } "
        "public function down(): void { " + down + " } };"
    )


class MigrationSafetyTests(unittest.TestCase):
    def test_additive_up_and_destructive_down_not_flagged(self) -> None:
        source = migration_php("Schema::create('module_events', function ($table) { $table->id(); });")
        self.assertEqual([], migration.violations(source))

    def test_drop_table_in_up_fails(self) -> None:
        self.assertIn("table drop", migration.violations(
            migration_php("Schema::dropIfExists('users');")
        ))

    def test_record_deletion_in_up_fails(self) -> None:
        self.assertIn("bulk deletion", migration.violations(
            migration_php("DB::table('applications')->delete();")
        ))

    def test_raw_ddl_in_up_fails(self) -> None:
        self.assertIn("raw SQL execution", migration.violations(
            migration_php('DB::statement("TRUNCATE TABLE applications");')
        ))

    def test_missing_down_requires_manual_review(self) -> None:
        self.assertNotEqual([], migration.violations(
            "<?php function up(): void { Schema::create('events'); }"
        ))


class BackupReadOnlyTests(unittest.TestCase):
    def test_synthetic_sql_and_tar_archive_integrity(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            base = pathlib.Path(tmp)
            db = base / "fixture.sql"
            db.write_bytes(b"CREATE TABLE sample (id INTEGER);\n" + b"-" * 1000)
            uploads = base / "files.tar.gz"
            with tarfile.open(uploads, "w:gz") as archive:
                data = os.urandom(4096)
                info = tarfile.TarInfo("private/documents/synthetic.pdf")
                info.size = len(data)
                archive.addfile(info, io.BytesIO(data))
            self.assertIn("SHA256=", backups.check(db, "db"))
            self.assertIn("SHA256=", backups.check(uploads, "files"))

    def test_archive_traversal_is_rejected(self) -> None:
        self.assertFalse(backups.valid_archive_entry("../other-user/data"))
        self.assertFalse(backups.valid_archive_entry("/etc/passwd"))
        self.assertTrue(backups.valid_archive_entry("storage/app/private/file.pdf"))

    def test_missing_backup_is_rejected(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            with self.assertRaises(ValueError):
                backups.check(pathlib.Path(tmp) / "missing.sql", "db")


if __name__ == "__main__":
    unittest.main()
