# JWTC Application Status Timeline — Release Gates

## Implemented on development branch

- Additive migration creates `application_status_definitions`, `application_status_events`, and `application_status_definition_events`. The migration seeds the exact **legacy IDs 1–11** and their existing progress percentages; no current application rows are rewritten.
- Legacy `getJobApplicationStatus*` helpers resolve the configurable catalog after migration and return legacy defaults if the schema is not installed.
- Admins with the existing `active-job` permission can rename stages, change percentages/display order, mark terminal stages, activate stages, or create additional stages. Changing the ID of an existing stage and removing stages are not supported. An in-use stage cannot be disabled.
- Admin and employer-owned API application status changes pass through an atomic transaction, preventing no-op duplicate events and logging the actor, previous/new stage IDs, stage name/progress snapshot, time, source, and optional **applicant-visible** note.
- Newly submitted applications also log their initial stage after the migration; historical applications keep their existing current stage. The system **does not fabricate past events**.
- Applicant's Applied Jobs list uses the configured current status and links to the private `/job_seeker/applications/{id}/timeline` view. Horizontal steps show workflow stages; vertical history shows actual recorded events.
- API `/api/jobseeker/applications/{id}/timeline` returns the status, stages and audited events only to the owner.
- Existing public URL/SEO routes, applicant documents and current rows are untouched by this development commit.

## Staging acceptance checklist

1. Confirm reliable independent staging database backup and restore (do **not** use production DB or destructive seeders).
2. Inspect the current deployed `applications` table and migration history. Apply **only reviewed additive migration** `2026_10_22_000003_create_application_status_workflow_tables.php` to staging.
3. Review admin `active-job` permissions; only authorized administrators should edit the stage catalog. Existing legacy employer status-management behavior is preserved and may need a business-policy decision before production.
4. Confirm stages **1–11** load with original names/percentages, including rejected/terminated at 0%, and that existing applications show their unchanged status.
5. Rename one stage and change its progress percentage in staging; check current progress changes but a historical audit snapshot remains unchanged.
6. Update an application from admin and employer-owned API, and verify status, timestamp, actor, note, and old/new IDs are recorded once; duplicate no-op updates create no event.
7. Verify another applicant cannot view the timeline, private notes, or API event JSON; verify unauthorized administrator configuration attempts are denied.
8. Check desktop/mobile stepper wrapping, horizontal scrolling, screen-reader labels, translations (UI is English), and usability with 20+ custom stages.
9. Verify new application code consumption and initial-status history are in the **same DB transaction**, and prior applications remain intact.
10. Run PHP syntax, PHPUnit, Blade compilation, Vite build and route inspection; follow with real browser/API staging tests.

## Important limitations

- Historical changes made before status auditing are **not reconstructable** from the current status column and are intentionally labeled as unavailable.
- Workflow percentage is an administrative display configuration; it does **not** guarantee any formal immigration stage or timeline.
- This feature does not itself implement passport/document locking, remote interviews, native chat, support/refunds, redesign, or production deployment.
- PR #3 stays **Draft**. No production `.env`, migrations or uploads are changed by the GitHub-only development.
