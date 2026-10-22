# JWTC — Data-Preserving Rebuild & Production Release Policy

**Owner instruction (2026-10-22):** Structural redesign and even full replacement of frontend/backend are allowed **only if existing records and uploaded files are preserved and demonstrably recoverable**.

**Current state:** Draft PR #3 only. No production migrations or changes were executed by this work.

## Non-negotiable invariants

1. The original **production database is the source of truth**. Rebuild code, templates, routing, APIs, schemas and UI as necessary; never rebuild live data from scratch, reseed applicant records, reassign user IDs or rewrite payment references. Keep old IDs and relationships intact.
2. Preserve all account/employer/application records, historical application status and audit events, job listings, roles/permissions, payments/subscriptions/refund records, private chat/support/OTP data, SEO/CMS settings and applicant documents.
3. Files are **not fully represented in Git**. Protect `storage/app` (including `storage/app/private`, encrypted documents, backup and email assets), `public/storage`, `public/uploads`, `public/images`, `public/documents`, `public/passports`, `public/cv`, `public/cvs`, `public/resumes`, `public/files`, `public/attachments`, `public/media`, and existing logos, theme bundles and user-generated content. Inventory actual production paths; this list may not be exhaustive. Preserve filesystem permissions and private/public access separation.
4. Preserve the exact active Laravel `APP_KEY` securely **outside GitHub and outside public webroot**. Encrypted private document/chat/SMTP/refund data becomes unrecoverable if the key is lost. Also preserve relevant `.env`, external SMTP, DNS/payment configurations and certificates in a secure password manager/secret vault, not as a Git commit.
5. **Never run on production without a verified, tested restoration:** `php artisan migrate:fresh`, `migrate:reset`, `migrate:refresh`, `db:wipe`, `schema:drop`, production seeders, destructive SQL, recursive cleanup of unknown folders, or `git clean -fdx`. Do not use `migrate:rollback` to undo a bad release: `down()` can drop NEW but already-populated tables. Roll back only versioned application code as appropriate; recover schema/data from a verified backup when specifically approved.
6. Feature-flagged modules remain OFF in production until independently tested. Do not merge a Draft PR or deploy just because unit tests passed.
7. A **data-loss-free claim requires evidence**, not just a successful CI pipeline. New migration static scans and seeded synthetic tests are defenses, not sufficient proof.

## Required backup set before touching production

- **A: Consistent MySQL snapshot** of *every relevant database/table* (including migrations table, schema, indexes, routines/triggers as applicable), preferably taken using a transaction-consistent `mysqldump --single-transaction --quick` from a least-privileged backup account. Verify a secure local **and separate off-host encrypted** copy. Do not publish .sql files or put DB passwords in shell command arguments or logs.
- **B: Full uploads + private storage copy** preserving original paths and names. Include all Laravel storage roots, public uploads/assets, legacy CV/passport folders and symlink target directories. Confirm private passport documents are not inadvertently restored into a public directory.
- **C: Secure configuration and encryption-key escrow**. Check exact `APP_KEY`, mail/payment credentials, cron/queue configuration and deployed asset versions are recoverable without exposing them in chat, PR or CI logs.
- **D: Verification evidence**: record timestamp, archive sizes and SHA-256 digests. A read-only utility is provided: `python3 scripts/release/verify_backup_archives.py --database /secure/path/db.sql.gz --files /secure/path/uploads.tar.gz`. Repeat `--files` for multiple upload archives. It does not back up data, restore it or send secrets anywhere.
- **E: Actual isolated restoration**: on a staging server and **separate database with fake/non-public access**, restore A+B+C, verify app boot, logins, application/job links, actual file checksums/download paths, encrypted fields and access permissions. Create independent sign-off notes and prove recovery time.
- **F: Database snapshots**: count and compare records per legacy table; verify counts of users, applications, jobs, employers, subscriptions, payment credentials, status history, CV and document metadata. Verify a sample of real file hashes and database-to-filesystem references without disclosing confidential data. `COUNT(*)` alone cannot detect missing or altered records.

### Special case: long-running rebuild

If users are still applying/uploading while a new version is developed, **the development snapshot will become stale**. Never switch traffic directly to that snapshot. Use a planned write freeze / maintenance window and capture final incremental changes, or a separately approved, fully verified CDC/replication process. Preserve primary-key relationships and reconcile row-level diffs before switching domains or databases. Resume writes only after validation.

## Safe deployment sequence

1. Produce immutable backup evidence and restore-test the exact release's source data. Include all user file directories and APP_KEY escrow.
2. Create a separate staging database and application root. Never point staging `.env` at the live database or shared live private document paths.
3. Review every new migration's `up()`; ensure it is **additive and non-destructive**. CI enforces a basic static guard under `scripts/release/check_migration_safety.py`; manual review is still mandatory.
4. On staging, apply `php artisan migrate --force` only after reviewing `php artisan migrate:status` and confirming the **staging DB hostname/name**. Capture row-level baseline before and after.
5. Test all existing user flows, admin permissions, jobs, application codes, private downloads, email rendering, SMTP and payment records. Verify old CVs and the current Gallagher imagery still resolve.
6. Run the browser/regression/security tests, generate a signed release checklist, and obtain explicit production cutover approval. Repeat backup close to the cutover, freeze/replicate writes as required.
7. Deploy application files/assets without deleting or overwriting private storage, old generated public uploads or original config. Reuse protected `APP_KEY`. Only run separately approved additive production migrations following an additional backup.
8. After release, compare row counts, row IDs, FK relationships and source-vs-target uploaded file hashes, verify uploads and downloads, inspect errors, and check live user writes before announcing completion.
9. If verification fails, stop writes on the faulty version. Roll back **code** to its previous release while preserving database updates; avoid reversing migrations on newly written data. Use the recovery plan based on validated backups and forward reconciliation when necessary.

## Warnings discovered in existing JWTC code

- The legacy admin database backup endpoint in `app/Http/Controllers/Admin/SettingController.php` streams a SQL export to the browser and calls `deleteFileAfterSend(true)`. It is a one-time download, **not a retained offsite/verified backup**. It also does **not** include user-uploaded files or APP_KEY.
- Many new migrations have `down()` implementations that drop newly created tables. Running rollback **after those tables contain live messages or private documents would delete those records**. Avoid rollback; safely revert application code and retain new tables instead.
- Old user-generated/public asset folders are excluded from Git and a staging Git checkout alone cannot reconstruct all existing images/CVs.
- Current CI uses synthetic SQLite data. MySQL/cPanel production compatibility, large-table locking and live encrypted file restore **have not** been verified yet.
- Existing administrative content-management code may delete old files on replacement. Code-level UI rebuild does not mean these content routes are safe for a bulk automated migration; handle originals by copy and validation first.

**Release-blocking principle:** Without two independent recoverable backups and an isolated, verified restore, do not touch production. Absolute zero data loss cannot be guaranteed without that evidence and a controlled cutover.
