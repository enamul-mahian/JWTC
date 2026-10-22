# Join Gallagher Responsive Website & Dashboard Redesign

**Status:** Implemented in Draft PR #3 development branch. Not merged or deployed. Existing public URLs, application data, CVs/passport files, legacy frontend assets and backend modules remain unchanged.

## What changed

- Opt-in visual refresh, controlled by `JWTC_REDESIGN_ENABLED=true`, implemented using a lightweight CSS file at `public/theme/jwtc.css`. **Default: false.**
- Site-wide theme tokens: navy, blue, teal, neutral backgrounds, consistent spacing and radii, cards, form controls, focus-visible outlines, reduced-motion support, responsive breakpoints and print handling. Existing Bootstrap and legacy page JS keep operating.
- New accessible homepage with semantic headings, relevant SEO description, keyboard-accessible skip link, current jobs search and cards from `jobs` database. A job is shown only when `is_publish=1` and `application_deadline` is not in the past. Unpublished/expired jobs are excluded. Empty state is honest; no fictitious recruitment counters.
- The legacy image-only Gallagher home page is left **byte-for-byte as an inactive Blade branch**, preserving the original `images/Gallagher.png` paths if rollback is requested.
- Public Homepage no longer fetches Google Maps scripts or Leaflet CSS when the redesign is enabled on the home route, avoiding unused page weight. Other pages' existing maps and APIs remain untouched.
- Existing Applicant and Employer dashboards gain responsive intro/navigation panels. Legacy profile/application/job data and existing JS controllers are retained.
- Admin Dashboard gets a new **database-backed** overview: actual applicant accounts, employer accounts, published job records, application records and five most recent jobs. No fake revenue or sample customer/employer notifications.
- Replaced old fake notification admin toolbar and demo link modal with a real accessible toolbar, working website link, profile, password and CSRF logout form. Sidebar remains the old functional/permission-aware one with refreshed styling.
- Privileged quick links appear only for existing authenticated permissions and feature flags (Private Documents, Interviews, Email Studio, Native Chat and Support/Refund Center).
- Frontend notification strings now use JSON-safe serialization rather than raw JS-string interpolation.

## Important constraints

- `JWTC_REDESIGN_ENABLED=false` preserves the old homepage layout and the old admin dashboard view, except minor safe template fixes such as favicon null-safety, corrected HTML list closing tag and JSON-safe flash text. The old public frontend and admin route names do not change.
- This is a **design-system-wide responsive refresh and new primary dashboard pages**, not a pixel-perfect redesign of every historic form, every data table or the original branding art. Some legacy forms and modals continue to use their prior markup, with the new scoped theme styles applied.
- The exact production staging visual appearance is not verified yet. Images, fonts, accessibility and spacing must be checked on the actual hosted site with real representative data.
- The homepage still uses the existing public job-search endpoint; the implementation does not change recruiting eligibility, refund policies or application security.

## Staging-only activation

1. Maintain the current production at its last verified working revision; keep Draft PR #3 unmerged.
2. Create an independent staging copy with a separately protected `.env`, database and **redacted** test accounts. Do not expose actual passports, CVs, chat ciphertext or real payment credentials.
3. Confirm `/theme/jwtc.css` and existing `frontend`, `backend`, `images/Gallagher.png` public assets are present on staging. Uploading Laravel PHP alone does **not** copy old images and bundles.
4. Set `JWTC_REDESIGN_ENABLED=true` in the **staging** `.env` and run `php artisan config:clear`. Verify that `config('jwtc_design.enabled')` becomes true.
5. Test guest homepage, search jobs, registration, applicant dashboard, employer dashboard, privileged admin overview, navigation, AJAX profile widgets, action menus, CSRF logout, permissions and access on the existing routes.
6. Test desktop 1440px, laptop 1024px, tablet 768px, mobile 390px and small mobile 320px. Inspect keyboard navigation, contrast, zoom 200%, reduced motion, long text and phone input styling.
7. Verify basic SEO metadata, correct canonical URLs, indexing directives, sitemap, lazy assets, navigation links and no accidental noindex on the public homepage.
8. Verify original branding/marketing images, all user upload/download links, third-party scripts and fonts still load; existing private files must stay outside GitHub/public web root.
9. Capture real screenshots of old and new views, profile/database counts and browser console/network failures before promotion.
10. Obtain explicit production release approval after passing integration, visual, security and SEO checks.

## Instant visual rollback

On staging, set `JWTC_REDESIGN_ENABLED=false`, then clear Laravel configuration cache. This switches the homepage and admin overview back to the previous templates without altering the database. For a production rollback, restore only the reviewed code/asset release after taking backups; do not overwrite user-generated uploads or database records.

## Validation

Automated tests in `tests/Feature/JWTCDesignRegressionTest.php` cover opt-in defaults, filtering expired/unpublished jobs, retaining legacy views, real dashboard KPI data and actual homepage HTML output. GitHub Actions compiles Blade, lints PHP, runs PHPUnit, builds Vite, and verifies routes.

**Production remains unchanged. Real visual staging checks remain outstanding.**
