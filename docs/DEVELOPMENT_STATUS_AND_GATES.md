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

## Verified after CI was introduced (2026-10-22)
- GitHub Actions run 39679567203 passed Composer installation, lint, 2 PHPUnit tests (14 assertions), and Vite build at commit 30453ff.
- The initial Composer failure was due to missing writable bootstrap/cache, then an eager database query in AppServiceProvider during Artisan bootstrap. Both have fixes on this branch.
- Expanded CI revealed that `php artisan route:list` currently fails because `App\\Http\\Controllers\\API\\AuthController` is absent from the repository. Other classes under `app/Http/Controllers/API` are also missing from the tracked source, while routes/api.php references them. API code must be restored from a verified trusted source or reimplemented against exact contracts before release.
- Protection against document deletion by other applicants and unsafe destructive GET routes is now implemented in the branch; associated regression tests were added afterward.
- Database export no longer uses concatenated shell commands containing plaintext command-line password arguments; it still requires real MySQL/export permissions and private operational backup controls on staging.
- Full historical DB/uploads/SEO/analytics checks, server runtime verification, API restoration, responsive UI redesign, OTP, chat, ticketing, refunds, interview features, and production deployment require independent completion and verification. Do not call this branch production-ready.
