# JWTC Development Status and Safety Gates

Updated: 2026-10-22. Source reference: main at 28a1a58. This report is based on repository files and is not a claim of runtime testing.

## Baseline
- Laravel 10 application (composer.json requires laravel/framework ^10.10 and PHP ^8.1).
- Main web flows: guest jobs, admin, employer and jobseeker dashboards.
- API auth uses Sanctum.
- Main repository README currently only contains # JWTC.
- Public build assets were not confirmed present; public uploads and production .env are intentionally not committed.

## Source-grounded findings
| Topic | Source | Finding | State |
|---|---|---|---|
| Web auth session | app/Http/Controllers/UserAuthLoginController.php | Session regeneration/invalidation absent from main | Patch prepared |
| One-time application code | app/Http/Controllers/JobSeeker/JobApplicationController.php | Application insert and code consumption not atomic in main | Patch prepared |
| Admin code approval | app/Http/Controllers/Admin/JobCategoryController.php | updateCode lacks dedicated validation/authorization shown in method | Needs implementation |
| Legacy code quota | app/Helpers.php | maximumAvailability returns 4 and eligibility is quota-bound | Business decision |
| Code schema | database/migrations/2024_10_25_225410_create_unique_codes_table.php | No application binding, approval history, expiry, or revocation fields | Design needed |
| Backup command | app/Http/Controllers/Admin/SettingController.php | exec shell command concatenates DB credential and paths | Security review required |
| Email settings | app/Http/Controllers/Admin/SettingController.php | Writes supplied credentials into runtime .env via regex | Security review required |
| Applications schema | database/migrations/2023_12_28_145510_create_applications_table.php | Existing application history requires additive migrations only | Preserve |
| OTP | Existing auth routes/controllers | No verified complete registration and password-change OTP flow | Audit required |
| Chat/interviews/refunds | Repository inventory | Complete first-party feature not verified | Audit and build required |
| SEO & analytics | application settings/views | Must compare existing values and frontend output before modifications | Preserve |

## Non-negotiable deployment gates
1. Obtain actual production DB schema inventory and separate encrypted database/uploads backups with restore proof.
2. Confirm PHP, MySQL, mail, queue and hosting capabilities; audit composer.lock and public assets.
3. Audit all write endpoints: web and API applications, admin code issuance and user role authorization.
4. Add feature, race-condition, authorization and regression tests; run PHP, Composer, PHPUnit and Vite build on staging.
5. Confirm business rules for code expiry/re-request/job binding, refunds, interview providers and status mapping.
6. Verify SEO meta/canonical/analytics against production and preserve all URLs/uploads.
7. Require explicit owner approval before production migration or deployment.

## Draft patches
- PR #1 introduced safe web session lifecycle handling.
- PR #2 branch now includes web session handling and atomic application/code submission.
- The patches are **unmerged**, **untested at runtime**, and do not make the requested product complete.
