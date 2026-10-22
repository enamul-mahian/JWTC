# JWTC Independent Staging Integration — Operator Procedure

**State:** GitHub Draft PR #3 only. This is NOT a production installation, verified backup, data migration, or approval to release.

## Objective and absolute boundaries

Protect production's existing database, applicants/employers, applications, documents/CVs/passports, SMTP settings, logs, site branding, uploads, SEO, payment metadata, and Laravel APP_KEY while validating replacement code in a **different application root and MySQL schema**.

Do not merge, use migrate:fresh/migrate:refresh/migrate:reset/db:wipe or reinitialize production. Do not copy real passports or sensitive financial records into publicly reachable staging directories.

## Read-only first pass (separate staging app)

The command below checks identity/permissions and reads schema metadata; it does **not** create a backup, restore, alter, write, migrate, switch symlinks or edit .env. Run it **only in a separately prepared staging clone**, with a distinct DB/schema and real upload-directory clones. Review code and replace every placeholder before running:

    cd /ABSOLUTE/STAGING/ROOT
    php artisan jwtc:staging-preflight \
      --expected-db='STAGING_MYSQL_SCHEMA' \
      --production-db='PRODUCTION_MYSQL_SCHEMA' \
      --expected-url='https://YOUR-STAGING-HOST.example' \
      --production-root='/ABSOLUTE/LIVE/APP/ROOT' \
      --uploads=storage/app \
      --uploads=public/images \
      --uploads=public/uploads

Requirements:
- APP_ENV=staging and APP_DEBUG=false; APP_URL is the expected HTTPS staging URL.
- The live app root exists and differs from the staging root (realpath is checked).
- The actual connected MySQL database must be exactly expected-db and **different** from production-db; SQLite/test database cannot pass. An ambiguous DATABASE_URL pointing to production must never be used.
- A valid APP_KEY must encrypt/decrypt a transient in-memory test string. Protect the key; do not print/store secrets in Git, chat, logs or screenshots. A valid *new* key is not proof that old encrypted data can be read; test restoration with the correct old key in isolated staging.
- At least two upload paths, including storage/app, must be explicitly checked. Supply all actual existing paths and inspect symlink targets; the command blocks paths resolved outside staging.
- Essential existing tables users/jobs/applications/migrations must exist, and migration history must be nonempty. If chat, Support or Email Studio are switched on, relevant new tables must also exist.
- If responsive redesign is enabled, staging public/theme/jwtc.css and public/build/manifest.json must be present.
- The command returns nonzero on any check failure and emits no SQL values or credentials.

**Limitations:** Correct names supplied as arguments do not guarantee independence if the operator misidentifies production. The command checks a live connection with SELECT DATABASE() but does not validate full table structures, contents, upstream DNS, shared storage outside listed paths, actual user authentication, keys from a real backup or recoverability. It never runs migrations or changes production.

## Mandatory backup and restore steps (separately)

1. Inventory live MySQL connection and relevant physical storage roots safely; record backup date/time and the list of all private/public application folders, symlinks and volume mounts.
2. With a reviewed backup procedure, obtain a transaction-consistent MySQL export containing all tables plus upload/private-storage archives. Store at least two independently secured copies with one off-host, not in public_html. Retain the correct original APP_KEY safely outside the backup archive sent to testers.
3. Inspect archives **read-only** with:
   
       python3 scripts/release/verify_backup_archives.py \
         --database '/SECURE/db.sql.gz' \
         --files '/SECURE/private-files.tar.gz' \
         --files '/SECURE/public-uploads.tar.gz'

   Output is only file sizes/digests and basic format/readability. Integrity of an archive is not proof of restored data.
4. Restore to an **isolated staging MySQL schema**, independently created/tested with appropriate database grants. Restore file archives into an isolated **staging** filesystem, preserving relative paths and privacy. Never extract archives into the live site or a shared production storage disk.
5. Independently verify record counts and **stable IDs** for users, applications, jobs, uploaded_cvs, jobseeker_uploads, jobseeker_certificates, documents/versions, payment credentials, subscriptions, chat and support records. Compare spot-checked row data and uploaded-file SHA-256 digests. Revalidate decryptability without exposing personal data.
6. Only after restore evidence exists: review pending migrations on staging, run additive migrations on staging (not production), re-run this read-only preflight, then complete web/API end-to-end workflows across real staging browsers and permissions.
7. Inspect application error logs, worker memory, upload inodes and email/provider configuration. Use test credentials and non-deliverable recipients for integration testing, not applicant email lists.
8. Verify rollback of application code while preserving new tables, files and writes. New migration down() methods are **not** an appropriate production rollback once data exists.
9. Record release approval explicitly before any production schema alteration, code merge or deployment. A successful CI check is not approval.

## Release-blocking evidence checklist

- [ ] Real production DB backup safely retained and checksum recorded
- [ ] Complete production file/private upload backup safely retained and checksum recorded
- [ ] Off-host copy and APP_KEY escrow verified
- [ ] True isolated database/files restore completed
- [ ] Data integrity checks: stable IDs, row/folder counts, hashes and references
- [ ] Staging preflight passed against actual restored staging DB (not simulated SQLite)
- [ ] Additive staging migrations reviewed and applied without changing legacy rows
- [ ] Real browser/API/SMTP/PDF/user-permission and upload/download testing passed
- [ ] Hostinger/cPanel runtime/SEO, error and performance checks passed
- [ ] Rollback rehearsal and explicit owner approval recorded

**Fail closed:** if any backup, restore, legacy file path, APP_KEY, migration or data comparison is uncertain, retain the working production release unchanged.
