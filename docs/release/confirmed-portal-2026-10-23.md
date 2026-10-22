# Confirmed portal and approval rules

The owner confirmed the London / Big Ben **Find Your Next Opportunity** portal showcase and **Job Application Progress Dashboard** as the design references. The earlier generic homepage was not the approved design.

Reference images: `JoinGallagher Job Portal Showcase.png` (Library ID `libfile_f2d02086b27081919d36703d48376a55`) and `Job Application Progress Dashboard.png` (`libfile_1231b05e564c8191b9bd1c68503554b2`). These are design references; production renders real database records rather than screenshot placeholder counts or jobs.

## Required behavior

- Public visitors can browse published jobs and job details without registration. Expired records remain visible with applications closed.
- Job seeker registration/sign-in and company registration/sign-in have separate entry points. A panel cannot authenticate the other account type.
- Newly registered companies are inactive and have no approval stamp. A pending or revoked company can only view the approval holding page and sign out. Both web and bearer-token API requests enforce this rule.
- An administrator with employer-list permission can approve or revoke company accounts. Every decision records the actor, time and optional reason. Revocation invalidates API tokens. Historical company accounts require explicit approval too.
- Poster email is hidden by default. The administrator controls publication through General Settings. Known receiving/contact addresses are redacted from public descriptions and serialized job data while hidden.
- Application tracking retains the owner-only access policy and real event history. Horizontal steps, percentage progress and the detailed timeline use the existing administrator-editable status catalog. Skipped stages do not acquire fabricated completion dates.

## Release constraints

The additive migration raises the migration count from 58 to 59. Preserve original rows, original columns, uploads, production credentials, APP_KEY, SEO and analytics configuration. Retain encrypted predeployment backup and private previous code. Validate the exact CI artifact on private staging before promotion.
