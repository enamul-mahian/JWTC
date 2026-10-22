# JWTC — Private Document Browser Workspace

**Development status:** Integrated in Draft PR #3. No production deployment or database migration has been performed.

## Browser views and routes

Applicants can access `/job_seeker/private-documents` from their account navigation to upload a private passport/certificate/other document, review previous versions, download only their own originals, and submit revisions if the file is not locked.

Authorized reviewers can access `/admin/private-documents` to filter the queue, inspect version history and review notes, securely download a version, approve, reject, request a revision, and lock/unlock sensitive files. All reviewer actions are recorded in `applicant_private_document_events` and the reason is applicant-visible.

These pages are **server-rendered web routes** (authenticated Laravel sessions, CSRF-protected forms) and intentionally do not depend on browser access to the token-oriented API routes. The UI uses a separate responsive `secure.layout` without Google Maps, analytics or other third-party JavaScript.

A dedicated response-header middleware sets no-store cache behavior, a script-denying Content Security Policy, no-referrer, no-sniff and deny-framing headers on private web pages and downloads. Existing public pages and routes are not changed.

## Reviewer permission — explicit grant only

Staging migration `2026_10_22_000005_add_private_document_review_permission.php` creates a Spatie permission named `review-private-documents` for guard `web`. It **does not automatically assign permission to any existing administrator**. Review endpoints require:
- authenticated account with `user_type` of `admin` or `super_admin`;
- **explicit** `review-private-documents` RBAC permission.

After reviewing the authorized staff list, grant the permission manually using the site's approved role/permission administration interface. Do not grant it just because staff can post or approve jobs. No applicant/employer can access the reviewer pages.

## Staging release checklist

1. Back up and independently verify the staging database. Review the existing migrations; apply the additive private document and review permission migrations only on an independent staging database.
2. Verify the private `local` filesystem disk is outside any public directory/symlink. Confirm private documents cannot be accessed by HTTP static paths.
3. Grant the dedicated reviewer permission to **only named authorized staff**. Test both permitted and denied admin accounts, including soft-deleted or disabled accounts.
4. Test the applicant's upload, original download, version revision, duplicate hash, lock behavior, file size/type, CSRF enforcement, and inaccessible cross-user record IDs.
5. Test reviewer queue, status transitions, rejection/revision notes, audit identity, and locked document refusal; ensure data cannot be retrieved without proper permission.
6. Confirm no external third-party JavaScript, static resources, browser cache, or link previews leak filenames or identity information.
7. Check mobile accessibility, keyboard navigation and complete reviews using actual staging identities; inspect server access logs.
8. Enable controlled malware scanning, encryption-at-rest/retention policies and backup restoration before allowing real passports.
9. Existing legacy/public uploaded CVs and certificates are **not** migrated by this feature. Preserve files and URLs, audit old public exposure separately, and never delete the old upload directories blindly.
10. Do not enable on production without complete compliance, staging and full Master Plan release review.

**Do not run production migrations, merge the Draft PR, or expose real passport documents based only on successful unit/CI tests.**
