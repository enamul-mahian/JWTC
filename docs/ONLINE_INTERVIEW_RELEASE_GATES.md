# JWTC — Online Interview Scheduling

**Status:** Implemented in Draft PR #3 only. Not merged or deployed.

## Capabilities

- Employer organizer can schedule appointments **only for applicants of a job they own**, and only if they have existing `post-job` permission. Both job.user_id and company.user_id must belong to the organizer.
- Admin or super-admin can manage interviews only with a separate, explicitly granted `manage-interviews` permission, created by migration without assigning to anyone.
- Appointment local time uses a validated IANA timezone; it is converted to **UTC for storage**. Time must be at least 15 minutes in the future and no more than one year away.
- Meeting URLs must be HTTPS, with no embedded user/password, and are visible only to the matching applicant or authorized organizer.
- Current status: `scheduled`, `confirmed`, `change_requested`, `cancelled`, `completed`.
- Applicant can confirm an upcoming interview or request a time change. Only organizer can reschedule, cancel or complete.
- Disallowed transitions are rejected. Historical actions and notes are recorded as separate, append-only audit events in the same database transaction as status/time changes.
- Application interviews are independent of existing 1–11 application progress stages. Interview activities do not invent or overwrite application status.
- Browser pages use authenticated sessions and CSRF-protected forms, without third-party JavaScript; API routes use Sanctum.
- This is scheduling and meeting-link management. **No native video transport, call recording, automatic reminders, or calendar synchronization** is represented as implemented.

## Files and routes

Migration: `2026_10_22_000006_create_application_interview_tables.php`

Admin permission migration: `2026_10_22_000007_create_interview_management_permission.php`

Applicant pages: `/job_seeker/interviews`

Organizer pages: `/interviews/manage`

API: `/api/interviews/my` and `/api/interviews/manage`.

## Release checks

1. Verify live database schema and back up staging DB; do not run migrations on production.
2. Apply both additive migrations only to independent staging.
3. Grant `manage-interviews` permission only to approved administrators through your authorized RBAC workflow.
4. Confirm employer/company/job ownership against actual legacy data, especially soft-deleted job records and employer permissions.
5. Test exact timezone handling for `Europe/London`, `Asia/Dhaka`, DST shifts, past dates, and local times that do not exist after DST changes.
6. Test a real HTTPS meeting URL supplied by an authorized organizer; no video room is automatically provisioned.
7. Test cross-account applicant/organizer ID requests, authorized admin access, cancellation and change requests, and audit invariants.
8. Test responsive browser forms and Sanctum API clients.
9. Verify SMTP delivery and retry policy, applicant privacy/retention, reminder delivery and official calendar integration **before treating this as a complete interview platform**.
10. Keep PR Draft and do not release until reviewed staging QA and full JWTC Master Plan sign-off.

**No production credentials, applicant personal documents, or real meeting tokens have been copied to GitHub.**

- SMTP email update delivery is best-effort, must be tested in staging, and currently has no retry queue.
