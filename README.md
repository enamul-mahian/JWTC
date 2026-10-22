# JWTC — Join Gallagher Recruitment Platform

Laravel recruitment portal and account workspaces (applicants, employers and authorized administrators).

## Critical: Data-preserving upgrades only

Do not wipe or reseed production. Keep the live MySQL database, user IDs, application histories, payment/subscription references, CVs, passports, attachments, public branding assets, private files, and **original APP_KEY** intact.

Read **[Data Preservation & Rebuild Policy](docs/DATA_PRESERVATION_REBUILD_POLICY.md)** before rebuilding, merging, running migrations or deploying. This policy requires consistent private/offsite backups, checksum evidence, **an actual isolated restoration**, comparison of old data and uploads, and an explicitly approved cutover. CI checks alone do **not** prove zero data loss.

New migrations are screened by `scripts/release/check_migration_safety.py`; backup archives can be inspected read-only with `scripts/release/verify_backup_archives.py`. Neither tool creates a backup, performs a restore, or changes production.

### Developer validation

```bash
python3 scripts/release/check_migration_safety.py
python3 -m unittest discover -s scripts/release -p 'test_*.py'
vendor/bin/phpunit --colors=never
php artisan view:cache
php artisan route:list --except-vendor
npm run build
```

The full CI definition is at `.github/workflows/ci.yml`.

### Feature flags

Most new modules and the progressive homepage/admin redesign are **disabled by default** until isolated staging migration and verification. For example, `JWTC_REDESIGN_ENABLED=false` retains the old layout. Other modules include online interviews, private document review, Email Studio, Native Application Messaging and Support & Refund Management. Permissions are never auto-granted to administrative roles just because a module exists.

Release checklists for each module are in `docs/`. Do not merge the development Draft PR or run production migrations until staging testing and explicit approval are complete.
