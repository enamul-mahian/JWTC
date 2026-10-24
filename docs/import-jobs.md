# Import Jobs (staged rollout)

## Goals
Admin-facing **Import Jobs** with two entry modes:
1. **CSV Upload** — rows with title, company name, location, salary, summary, requirements, deadline, external application URL, and optionally source URL / source ID.
2. **Import from Link** — paste one job-detail URL or multiple job-detail URLs (one per line). Preview parsed structured job information before saving. Source-specific adapters may later support a listing/search URL with pagination, but a listing URL must not be assumed equivalent to one job.

## Workflow
Import → normalize → validate → preview → stage in `job_import_batches` and `job_import_rows` → admin review → publish.

Publication is not enabled in this initial migration. Never insert unverified rows directly into `jobs`, because existing jobs require employer/user, company, category and country FKs and publication rules. Administrator maps verified employers and mandatory fields before publication.

## Safety and data integrity
- Source attribution and the authentic external apply URL are retained. Source URL may be internal metadata, but attribution requirements of the origin must be honored; do not pretend imported positions were posted directly by a company on JoinGallagher.
- Confirm source site terms and reuse permissions before fetching or republication. Do not scrape access-controlled or blocked resources.
- Use server-side URL validation: HTTPS only, DNS resolution with private/loopback/link-local/metadata address rejection, redirect-by-redirect validation, timeout, byte limit, no arbitrary HTTP headers/cookies.
- Preserve raw salary units; do not guess unspecified compensation, location, sponsorship or closing dates.
- Normalize source IDs and canonical URLs for dedupe. Deduplicate both within batch and against prior imports and published external URLs.
- Flag expired roles, removed sources and incomplete listings for review.
- Use allowlisted providers / approved HTML structured data adapters, not a universal unbounded crawler. Rate limit and cap batch sizes.
- Only user-authorized admins can stage, review and publish. Include CSRF protections, audit logs, and publish confirmations.
- Re-write descriptions only with lawful permissions and without fabricating employer details.
- Never store source login credentials, and never replace existing user-owned employer listings without explicit mapping and safeguards.

## CSV schema (proposed)
`title,company,location,employment_type,workplace,salary_min,salary_max,salary_currency,salary_period,summary,requirements,deadline,apply_url,source_url,source_id`

## Next implementation steps
Add an admin route/controller for secure CSV parse and URL preview; form request validation; staged list UI; company resolution workflow; duplicate checker; service-specific URL adapters; publish service; feature tests. Expose menu text `Import Jobs`.

## Current implementation
Only the staging migration has been added in this branch; no controller, web UI, parser, sync automation, or publication action is yet implemented.
