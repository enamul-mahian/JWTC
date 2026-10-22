# JoinGallagher legacy SQL compatibility audit — private owner-supplied dump

**Status:** Historical schema compatibility TESTED on disposable MariaDB. Real production backup/restore, live user rows and uploaded files NOT tested. Do not merge or deploy to production.

## Grounded source identification

- Source: owner-provided local SQL dump named `dropoqux_jobs.sql`, inspected read-only.
- Private full-dump SHA-256: `0b427d27abda9874186d56a4d1d09025cd1136491227f7b9d301246a63b4fd92`.
- Original export engine: phpMyAdmin / MariaDB 11.4.13. This does **not** establish that the dump is the latest production data as of any later deployment date.
- Discovered **46 legacy tables** and **43 applied historical migration IDs**, none missing relative to the **43 historical migration files** on the development branch.
- Branch has **15 new additive migrations**, numbered `2026_10_22_000001` through `2026_10_22_000015`; total after migration would be **58**.
- The owner-supplied schema contains the primary required fields in `users`, `jobs`, `applications`, `companies`, `unique_codes`, `payment_credentials`, `uploaded_cvs`, `jobseeker_uploads`, `jobseeker_certificates`, and `migrations`. No missing *required* columns were found in this limited check, including `users.user_type` and `applications.expected_salary`.
- Legacy `applications.employer_id` refers to **companies.id**, not `users.id`. Preserve this meaning when extending the interview/chat/application workflows.
- Existing `users.job_category_id` is required and should not be silently removed or reset. Preserve all existing foreign keys, record IDs and uploaded-file references.

## Tests based on the real schema — without private rows

The private SQL dump **was not committed** to GitHub.

We extracted only DDL and migration **names** into `tests/fixtures/legacy_schema_only.sql`:
- 46 CREATE TABLE definitions.
- 109 ALTER TABLE definitions (keys, constraints and auto-increment column attributes). Next-ID counters were normalized to 1, which cannot restore existing data by itself.
- 43 synthetic `migrations` entries with historical names and synthetic batches.
- **Zero** real INSERTs for applicants, CVs, payments, employers, jobs or other private records.

GitHub Actions now has a disposable `legacy-schema-upgrade` job using a temporary **MariaDB 11.4** service. It restores the schema-only fixture, verifies 46 original tables and 43 already-applied migration names, runs the **15 pending new migrations**, reruns migrations to confirm idempotence, and verifies all 58 migration markers and new table presence.

The fixture importer `scripts/release/load_legacy_ci_fixture.php` is **hard-blocked** unless APP_ENV is `testing`, the database is exactly `jwtc_legacy_ci_only`, DB host is loopback, the exact disposable CI marker is present, and the APP_URL matches the fixed test URL. It must never be used against production or staging.

A separate `mysql-schema` GitHub Actions job continues to run all 58 migrations from a clean temporary MySQL 8 installation. The historical SQL source came from MariaDB, so **the MariaDB upgrade simulation is the stronger structural comparison**.

The read-only `scripts/release/audit_legacy_sql.py --sql /PRIVATE/PATH/dropoqux_jobs.sql` checks critical column names, historical migration IDs and new-table name collisions without importing data or printing actual SQL rows. Its self-tests reject missing legacy columns and dangerous table collisions.

## Exactly what this proves, and what it does not

**Proves (structural, synthetic CI environment only):**
- New tables and schema changes can be applied on the supplied historical 46-table DDL layout in disposable MariaDB 11.4.
- The 43 supplied historical migration names match the 43 historical repository migration files.
- The 15 new migrations run successfully **when imported history and legacy schema are consistent**.
- No existing table is dropped or recreated by the 15 migrations in the schema-only simulation.

**Does NOT prove:**
- A real data-containing SQL backup restores successfully with row contents, constraints, views, triggers or app-specific states preserved.
- Existing applicant CVs, passports, private documents, emails, passwords and payment credentials remain readable, as the anonymized fixture contains no original records or files.
- The dump is current or matches the **present live production schema** (new users/application/uploads may have been added after the original export).
- Correct APP_KEY, user upload filesystem mapping, production MySQL grants, runtime mail provider, inode limits and cache assets.
- Actual site-wide accessibility, browser messaging, payment/refund settlement or GDPR/data retention acceptance.
- That data will be preserved without independently checked offsite backups, isolated full restore, row/file reconciliation and write-freeze or controlled synchronization.

## Additional historical ZIP inventory (read-only)

Two earlier private website/source archives were inventoried **without extracting or deploying them**, and without opening credential values or applicant content:

- The approximately 271 MB historical \`joingallagher.uk.zip\` contains website source and public assets, including a substantial set of public images, plus **older** job portal SQL exports (including a 46-table export from 2024-era naming).
- The approximately 36 MB \`JoinGallagher-Source-Code.zip\` also contains that older 46-table SQL export. These are **not the same identified database export** as \`dropoqux_jobs.sql\`, and must not be substituted for the latest production DB.
- A historical \`.env\` file is present in the full website archive. **Treat the archive as secret-bearing**, do not commit it, republish it or expose it in a public web folder. Its APP_KEY/passwords must not be assumed current; preserve the actual running deployment's APP_KEY via authorized vault.
- No \`public/storage\` directory and no \`storage/app/private\` directory was found in those ZIP central-directory inventories. \`storage/app\` had very few files. This **does not prove no such user uploads exist on the server**; it means these archives cannot be presumed complete backups of existing applicant documents.
- A Git source checkout plus these archives is not proof of file completeness. Independently back up all real current upload locations and symlink targets; verify SHA-256 hashes and authorized private downloads before releasing.

Only archive member counts, categories and SQL DDL metadata were examined; no private row/secret values or personal filenames were copied into the repository.

## Mandatory next acceptance gates

1. Obtain and preserve a **current** read-only snapshot of production MySQL and all public/private uploads, with independent encrypted off-host backups and APP_KEY escrow.
2. Compare its actual table/column and migration history to this historical snapshot. Any new/changed live schema needs a fresh safety review, not blind migration.
3. Restore the current full backup, including existing rows, on an independently permissioned staging host and separate database. Never point staging at production storage.
4. Run `php artisan jwtc:staging-preflight` and verify correct staging DB, HTTPS URL, APP_KEY fingerprint, file paths and authorized feature schemas.
5. Apply the 15 migrations **only to restored staging** after taking a staging snapshot; compare stable row IDs, counts, FKs and old/new private file SHA-256 values. Test real logins, applications, CV/passport download, interviews, email and support workflows.
6. Sign off restore proof, browser E2E, rollback, SEO/analytics, private access and performance before any production change.

**Result:** Historical SQL source recognized and structurally upgrade-tested. **Production remains untouched.**
