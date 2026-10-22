# JWTC — Owner Master Instructions and Development Handoff

Updated 2026-10-23. Private repository enamul-mahian/JWTC. Development branch fix/atomic-application-code-20261022. Pull Request #3 is **Draft**; no production deployment or main merge authorized.

## Binding instructions from owner

Continue all previously requested development without repeatedly asking for already supplied instructions. Complete features coherently with backward compatibility and fix failing tests. Use English user interfaces.

**Absolute data-preservation requirements:** Preserve all live applicants/employers, historical applications and statuses, roles/permissions, current database IDs/relationships, private CV/passport/files, payments, subscriptions and refunds, active URLs, SEO, Analytics, stored page content, logos, assets, APP_KEY and .env. Do not overwrite live uploads, delete tables, reset passwords, reseed production, expose hCaptcha/SMTP secrets, merge draft changes, or perform production migrations or deployment without verified backup/restore and explicit owner approval. Shared/cPanel hosting; staging must have an independent private root/database.

## Full feature plan (code availability does not imply release approval)

1. Secure sessions, login/register/password workflows, hCaptcha, registration and password-change email OTP, admin privilege checks and any approved login 2FA.
2. One-use job application codes, atomic submission, historic eligibility and quota behavior, applicant-only workflows, duplicate prevention.
3. Admin-configurable application status labels/order/percent, real immutable audit histories, applicant timeline/progress, scoped employer/admin transitions.
4. Private applicant CV/passport/identity documents, versioning, locking, review and downloads. Preserve legacy file URLs and paths.
5. Online interview scheduling, role-aware management, timezones, secure meeting links and notifications.
6. Native private application chat, unread tracking, pagination, moderation, XSS/tenant isolation, shared-hosting polling, retention and attachment scanning.
7. Private complaints/support tickets, manual refund approval, independent second-officer settlement verification, immutable financial audit.
8. Email Studio with Gmail-compatible rich HTML, font and size controls, pasted formatting, visual drag/X-Y image positioning, PDF attachment templates, separate encrypted SMTP profiles and real delivery tests.
9. Premium responsive public/admin/employer/jobseeker dashboards, real metrics, proper navigation/permissions, accessible design, current branding/assets.
10. Secure job posting/moderation, salary bands and hourly/daily/weekly/monthly/yearly pay periods, site settings, customizable branding, CMS/page builder and timezones.
11. Preserve and verify production SEO titles/descriptions/canonical URL structure/sitemaps, Analytics/Search Console settings and existing integrations.
12. Run all PHP/Composer/Blade/frontend/PHPUnit CI, isolated staging browsers/API, security/privacy, MySQL/MariaDB migration, upload/backup restoration and end-to-end tests.

## Existing branch status at handoff

- Draft PR #3 already includes session hardening, recovered Sanctum APIs, hCaptcha, OTP, atomic application code redemption, status timeline, private document review, interviews, Email Studio/visual PDF/SMTP, native chat, support/refund review and opt-in responsive public/admin dashboards.
- 15 new additive migrations; the previously supplied private SQL contained 46 legacy tables/43 historic migrations. A sanitized DDL-only fixture passes MariaDB 11.4 upgrade to 58 migrations; fresh MySQL 8 CI also passes. No private applicant rows were committed.
- Guarded synthetic row SHA-256 preservation checks simulate pre/post-migration employer, applicant, job, document, payment and SEO values. This does NOT establish full real data preservation.
- Security patches now scope employer web applicants/CV views/shortlisting to employer-owned jobs, audit shortlist/interview status transitions, restrict employer job edit/archive/restore, ignore client owner/company/publish fields, require admin job publication, preserve historic job slugs, and remove shared job-form cache.
- Job form salary validation/value duplication fixed; hourly and daily pay periods added.
- Canonical admin and historical Admin user type spellings accepted, excluding ordinary staff and employers.
- Legacy admin SMTP editor now hides the stored password, requires proper permissions, never rewrites .env, and tests saved SMTP by explicit CSRF POST with outbound host restrictions. Main Laravel mail still depends on hosting MAIL_* configuration; test actual deployment behavior before switching mail configuration.

## Release BLOCKERS — do not claim these are finished

- Restore CURRENT production MySQL and ALL relevant upload folders plus safely escrowed original APP_KEY to a private separate staging server; do not substitute old SQL/ZIP archives for current production backup.
- Verify table counts AND row hashes, file hashes, archived user uploads, encryption/key, permissions, old URLs, old analytics/SEO settings, original asset references and rollback plan. Monitor changes made to the live site after the backup.
- Test real SMTP Gmail HTML/PDF and OTP delivery, live hCaptcha hostname with ROTATED previously exposed secret, private files, interview timezones, chat policies, refund claims, API/mobile payload compatibility, job workflows and all responsive pages.
- Check pending admin OTP/login 2FA, full page builder, brand/timezone controls, any unrecovered old assets, real hosted mail behavior, payment/refund workflows, accessibility and privacy/retention requirements.
- Keep new feature flags OFF until individually validated. Do not touch live .env, APP_KEY, DB, uploads, client records, homepage SEO or asset store without explicit authorized migration/deployment.
- Require a complete staging sign-off, offsite backups, restore rehearsal, write cutover plan and OWNER authorization before production release.

## Auto-continuation protocol for the next chat/developer

1. Read this file, README and module release-gate docs. Fetch the LATEST head of fix/atomic-application-code-20261022 and Draft PR #3, plus the actual most recent CI workflow results. Do not assume this document is updated on every commit.
2. Implement the highest-priority missing product/security/release requirement in development branch; write authorization, data preservation and browser regression tests. Never insert or publish real private user data or secrets in Git.
3. If the latest GitHub CI job fails, inspect failure logs and fix it before calling a change completed. A previous green run does not prove the current commit is good.
4. Keep compatibility for historic routes and mobile/API contracts. Confirm all changes with staging E2E and backup/restore before release. Never merge PR #3 or deploy without owner approval.
5. Do not re-ask owner for instructions already documented. Ask only when an actual missing external credential/resource/permission is necessary for operations (and never request a secret in public text).

See docs/DATA_PRESERVATION_REBUILD_POLICY.md, docs/LEGACY_SQL_SCHEMA_COMPATIBILITY_AUDIT.md, docs/STAGING_INTEGRATION_PRECHECK.md and other module-specific RELEASE_GATES documents.
