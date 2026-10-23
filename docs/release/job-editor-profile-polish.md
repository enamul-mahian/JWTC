# Job editor and applicant profile release

Live deployment: 2026-10-23, 20:32 Asia/Dhaka. PR #17; tested source 42c08971d8ce364e4f3c9e6ea6ee785df90c2eb9. CI 39969764609 passed all four jobs: 174 PHP tests / 1311 assertions, compiled Blade syntax, JavaScript syntax, frontend build, dependency audits, MySQL 8 and MariaDB preservation.

Deployment run 39970137237 succeeded. Deployment ID 20261023-143157-1b83d4d1; verified encrypted backup 20261023-143125-72853a3a. Original rows, uploads, environment and APP_KEY retained. Migration count 64. One-shot push trigger restored to manual dispatch.

## Delivered behavior

- Independent checkbox choices for job types, skills, levels, workplace and gender; duplicate skill labels consolidated. Visual rich text editors preserve formatting without displaying raw markup.
- Default job locations: United Kingdom, Scotland, Ireland, UK Territories. Admin > Job Locations adds/removes choices. Removal retains country records and saved job locations. Public search follows the managed list.
- Admin/employer job editor accepts validated PNG/JPEG/WebP logos up to 2 MB. Job cards and public details display the uploaded logo. Editable FAQ questions/answers are generated from job details; users can add/remove/regenerate. Poster-email privacy applies to FAQ display and serialization.
- Applicant profile uses readable cards, native personal/contact/address editing, a private passport section and links to private passport/CV/document uploads. Existing experience, skills, language, education and training tools remain. Passport numbers are encrypted, masked on display and checked for duplicates; details lock after application submission. Legacy applicants can supply a missing passport record once.
- Company registration defaults to Others while allowing changes.
- Admin > Settings > Human Verification configures hCaptcha using encrypted secret storage. Existing server validation applies to both web and API authentication. Secret values are never shown or flashed.

## Verification and remaining configuration

Read-only production rendering passed nine pages, including admin job editor, employer job creation, applicant profile and managed locations. No real accounts/jobs/messages were created during verification. Authenticated browser visual QA was not performed.

Public browser verified Others selected by default, exactly four managed location choices, readable blue/white submit button and working public FAQ disclosure. No site JavaScript errors observed (browser extension metadata warnings excluded).

hCaptcha live configuration has neither site key nor secret and is therefore disabled. The settings form prefills the previously supplied public site key. The owner must enter the secret in Admin > Settings > Human Verification and enable it; no secret has been committed to the repository. A real hCaptcha challenge was not attempted.
