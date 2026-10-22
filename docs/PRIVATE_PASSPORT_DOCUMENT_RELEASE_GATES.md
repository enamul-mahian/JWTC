# JWTC — Private Passport and Document Management

Status: **Draft development only. No production deployment or merge is authorized by this document.**

## Implemented

- A separate, additive private document schema stores applicant-owned passport, certificate and other document records.
- Each document can have an immutable version history. Original files are stored on Laravel's `local` disk under `storage/app/private-documents/<user-id>/` and **not** in the webroot.
- A SHA-256 checksum and a database unique constraint prevent the same applicant uploading identical file contents more than once, even under a different filename.
- At most one **passport document record** can be created per applicant through the new upload API; replacements must be submitted as a revision of that record.
- Applicant JWT/Sanctum-protected APIs allow upload, revision, private version download and review history. The applicant can access only their own document records.
- Admin review endpoints require the admin or super-admin account type **and** the existing `active-job` permission. Reviewers can lock, unlock, approve, reject and request revision. Decisions are recorded in the audit table. Locked documents cannot be revised.
- Both filename and private disk path are intentionally hidden from list/metadata responses. A guarded download route streams the private file and verifies its owner and path prefix.
- No changes were made to old CV/certificate/public-upload tables. Their migration to private storage is **not yet complete** and requires a separately reviewed plan to preserve existing links.
- API exception handling was made compatible with JSON errors, avoiding missing HTML template failures.

## Staging checks before enabling

1. Verify live schema and existing passport/document upload directories; protect existing historical records and applications.
2. Back up the staging database and applicant attachments. **Never copy a production .env, secrets or personal files to GitHub.**
3. Run the additive private document migration **on staging only** after an independent backup, not on production.
4. Confirm storage disk `local` is outside the publicly served webroot. On shared hosting, ensure no symlink or rewrite rule exposes `storage/app/private-documents`.
5. Test PDFs and images, size/MIME enforcement (10 MB maximum), duplicate content rejection, checksum collision protections, revision history and backup restore.
6. Verify applicant A cannot list/read/download applicant B's documents and employers cannot use applicant or admin routes.
7. Test authorized admin approval, rejection, request revision, lock/unlock and audit. Confirm locked applicant documents cannot be changed.
8. Add malware scanning, retention/deletion policy and credential/permission audit before production. Test privacy controls and data export obligations applicable to passports.
9. Verify mobile/web frontend integration. New API endpoints exist, but dedicated applicant/admin passport screens are not yet implemented.
10. Run CI and full real browser/mobile/API staging tests. Production must be separately authorized.

## Remaining release risks

- Existing legacy public applicant uploads remain separate and may still be directly web-accessible until a safe private migration and URL/access-control review.
- Malicious-file scanning and asynchronous processing are not implemented.
- Duplicate identification is by **file checksum within one applicant account**, not a global check of passport number across people.
- Reviewer roles/permissions need confirmation against the actual production RBAC configuration.
- A production storage retention, encryption-at-rest and audit access policy still requires implementation and verification.
