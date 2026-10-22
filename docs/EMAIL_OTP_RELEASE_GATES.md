# JWTC Email OTP — Staging and Release Gates

**Status:** Development branch implementation only. Neither the main branch nor live hosting is changed by this work.

## Current design

Password change:
- Employer, Jobseeker, and API account owners must first supply their **current password** and a new password.
- When `PASSWORD_CHANGE_OTP_ENABLED=true`, JWTC sends a six-digit code to the account email; the password remains unchanged until verified.
- A server-side database challenge stores a salted code hash and **only the new password hash**.
- Codes expire in 10 minutes, permit at most five incorrect attempts and have a 60-second resend cooldown.
- Browser verification logs the user out after success. All Sanctum API tokens are revoked.
- Unverified/expired/mistyped code does not change the password.
- Existing installations retain the legacy password-change flow until the flag is enabled.

Registration:
- When `REGISTRATION_EMAIL_OTP_ENABLED=true`, JWTC validates the registration form (and optional hCaptcha/referral code), then emails a six-digit code.
- Account records and API tokens are created **only after** code verification; registration payload is encrypted using the Laravel APP_KEY with passwords already hashed.
- The registered email is marked verified after the code is consumed. Web sessions are regenerated and API clients receive a token after confirmation.
- API clients must use `POST /api/registration` then `POST /api/registration/verify` with `verification_id` and `otp`.
- Browser users use `/register/verify-email`. Both employer and jobseeker registration paths are supported.
- Codes expire in 10 minutes, with a five-attempt limit and a 60-second cooldown.
- Existing registrations retain prior behavior until the flag is enabled.

## Required staging checks

1. Confirm the **actual staging database schema** matches all legacy model migrations, especially `users.job_category_id`, `personal_information`, `education`, `employer_information`, roles, and permission pivots.
2. Confirm a fresh staging DB backup/restoration procedure, production user uploads/data isolation, stable APP_KEY and properly configured mail sender/SMTP. **Do not copy live .env to GitHub or staging**.
3. Apply additive migrations for `email_otp_challenges` and `pending_registrations` to **staging only**, after verifying the migration plan. Never migrate directly against production without a reviewed backup and rollout.
4. In staging's own protected `.env`, set:
   ```dotenv
   REGISTRATION_EMAIL_OTP_ENABLED=true
   PASSWORD_CHANGE_OTP_ENABLED=true
   ```
   Then `php artisan config:clear` within staging only.
5. Complete real inbox tests: both browser registration forms, authenticated Jobseeker/Employer password changes, API register + verify, API password request + verify, missing/wrong/expired/reused codes, cooldown, five wrong attempts, and mail outages. Confirm hCaptcha is enabled and enforced where intended.
6. Existing legacy users with `email_verified_at=NULL` are not automatically locked out. Audit whether a separate progressive verification policy is desired.
7. Configure cPanel cron with a reviewed command to invoke Laravel's scheduler (or directly invoke `php artisan email-otp:prune`) for daily cleanup of expired encrypted pending registrations.
8. Use GitHub Actions tests as one gate, not as a replacement for staging browser/API and live data-compatibility testing. Check dashboard/email templates and all historical SEO/analytics/asset behavior.
9. Production activation remains a **separate release decision**; this document does not authorize a merge or deployment.

## Limitations

- Direct SMTP deliverability and host/domain hCaptcha configuration have not been validated from GitHub Actions.
- This does not yet add two-factor login or admin password change OTP.
- Legacy public applicant files need a separate private-storage migration with safeguards.
- New browser registration requires an available job category; confirm existing production categories and table constraints before activation.
- Full JWTC master-plan features (interviews, live chat, tickets, refunds, advanced status timelines, redesign, SEO, passport locks) require separate development and QA.
