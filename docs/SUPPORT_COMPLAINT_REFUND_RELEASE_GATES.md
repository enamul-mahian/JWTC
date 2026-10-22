# JWTC Support, Complaint and Refund Review — Deployment Gates

**Development branch:** Draft PR #3. Existing production website/database have NOT been changed, and no live payment refund has been executed.

## Scope and behavior

- Applicants and employers may create account-scoped Support Tickets categorized General, Complaint, Payment, Refund, Account, Documents, Technical.
- Ticket descriptions and all replies are encrypted in the private database with Laravel APP_KEY; no passwords, OTP codes or payment card numbers should be included. Ticket subjects remain searchable plaintext and must not contain sensitive personal information.
- Users see only tickets they created, subject to the existing account session or Sanctum API token. Staff/admin may access tickets only with explicit `manage-support-tickets` permission. The dedicated permission is created, but not granted by migration.
- Ticket lifecycle: Open → In Progress / Pending Customer → Resolved / Closed; customers can reopen their own resolved or closed tickets. Staff may prioritize, update statuses and leave encrypted internal notes.
- Support managers can assign or unassign tickets only to active approved admin/super-admin users who individually have the manage-support-tickets permission. The assigned staff ID is stored on the ticket, and assignment events include the acting manager and selected staff ID.
- Separate private refund requests can be made only by the ticket owner on Payment or Refund category tickets. Currency supported: USD, GBP, EUR, BDT. Claimed payment reference and reason are encrypted; amounts are stored as integer minor units to avoid floating point rounding.
- Only one refund request is supported per ticket; creation and decisions are transactional and recorded in append-only events.
- Dedicated admin reviewer permission `review-refund-requests` allows Requested → Under Review → Approved / Rejected, with a mandatory decision reason for approval/rejection. **Approval is a case decision, not a payment instruction or transfer.**
- Distinct permission `confirm-refund-settlement` allows Approved → Settled only after a verified external settlement reference is supplied. Settlement officer **must differ from the approver**, enforcing a second reviewer.
- **No automatic Stripe, PayPal, bank or other gateway refund API call exists.** Staff must independently verify that a corresponding original charge exists, refund eligibility, currency, customer ownership and that the real payment was issued through approved external accounting/provider processes.
- Responsive private web UI, CSRF protection and authenticated account pages. API requests use Sanctum; rate-limited write endpoints and private no-store/CSP response headers. No file attachment uploading (pending malware scanning and sensitive-document review).

## Configuration and database

- Additive migrations only:
  - `2026_10_22_000014_create_support_ticket_and_refund_tables.php`
  - `2026_10_22_000015_create_support_review_permissions.php`
- Feature flag `SUPPORT_CENTER_ENABLED=false` by default in `config/support_center.php`. Set to true **only after staging is tested and approvals are recorded**.
- Dedicated admin permissions (all default ungranted): `manage-support-tickets`, `review-refund-requests`, `confirm-refund-settlement`. Define reviewer and settlement staff assignment separately.
- Routes: `/support-center` with customer and admin ticket dashboard; private ticket conversation and refund panel. REST API under `/api/support-center`.

## Required staging verification

1. Back up staging database, prove restore, inspect legacy subscription/payment records and inspect the payment provider's original-charge identifiers.
2. Run additive migrations on isolated staging database, never on current production until signed off.
3. Grant separate role permissions to named staff. Validate ordinary admins, guests, employers, and another applicant cannot inspect account tickets.
4. Test complaint creation, reply encryption, internal-only notes, priority/status flow, reopening and pagination.
5. Test manual refund request, duplicate protection, minor-unit values, reviewer approval/rejection, settlement proof, and failure to settle with the same approver.
6. Verify no claimant can alter reviewer statuses/amounts, mark requests settled or issue refunds automatically. Compare against actual Stripe receipts **using approved finance operations**.
7. Verify encrypted backups, application key recovery, incident escalation, retention policy, personal data access, abuse monitoring and data export requirements before production.
8. Test mobile browsers, Sanctum clients and SMTP notification plans. Automated email alerts and refund processing are not assumed implemented.
9. Load test support queue under Hostinger/cPanel resource limits.
10. Keep Draft PR open; do not deploy without full end-to-end staging tests and explicit release approval.

**Future dependencies:** Automated provider refund reconciliation, reviewer assignment workflow, encrypted attachment upload and malware scanner, SLA/escalation timers, customer notifications, support analytics, account deletion/retention rules, integration with verified payment transaction IDs. None are claimed complete.
