# Registration and company approval repair

The production registration error was confirmed by privacy-filtered server diagnostics: email OTP was disabled; `users.job_category_id` was NOT NULL with no default; the non-OTP registration controller omitted that value. The request failed at RegisteredUserController.php:132 with SQLSTATE HY000. No raw logs, customer details, credentials or registration codes were exported.

The registration form now always shows Preferred Job Category. The controller validates and saves it, restricts phone numbers to the existing 20-character column, checks that the Job Seeker role exists, creates the account/role/personal profile together and regenerates the login session. Validation errors appear in a visible summary and preserve non-password input. Existing OTP/captcha configuration is unchanged.

Admin navigation now includes a top-level Company Approvals entry for administrators with the existing employer-list permission. The review page defaults to pending companies, shows their count, supports approved/all filters and displays successful decisions. Existing approval/revocation auditing and pending-company access restrictions remain in force. No real company was approved as part of this release.

Job cards no longer reserve empty title, metadata and summary rows. Their padding and vertical spacing are reduced while equal row heights and footer baselines remain supported. Shared page container alignment is retained.

Validation: CI 39954348336, 163 PHP tests / 1186 assertions, frontend/service-worker checks, MySQL and legacy MariaDB preservation checks. New tests exercise complete non-OTP registration against the real NOT NULL schema, missing category, invalid category/phone, missing-role retry, duplicate submission, and the approval screen/navigation/authorization. No new migration or customer-data changes are required.

Live registration was inspected without submitting a synthetic account; successful account creation is covered by isolated tests. Administrators use `/admin/login`, then Company Approvals → Pending approval → Approve.

Live deployment run 39954685797 succeeded. Browser verification confirmed Preferred Job Category and its populated options on `/register`, stylesheet version 20261023-4, and card heights reduced from 277.97px to 233.53px with matching footer baselines. All existing rows, uploads and the encryption key passed preservation checks; the migration count remains 62.
