# JWTC Native Application Messaging — Release Gates

Development branch, Draft PR #3. Not merged, enabled or deployed to production.

## Implemented
- Application-scoped private chat for applicant and employer: one thread for each submitted job application. Job and company must both be owned by the sending employer.
- Applicant can start and use only their own application thread. Employer needs existing post-job permission. Admin or super-admin needs explicit manage-application-chat permission; that permission is created but not automatically granted.
- Message bodies are encrypted using Laravel Crypt with the protected APP_KEY and stored privately in the application_chat_messages table.
- Browser inbox and conversation page use Laravel web sessions and CSRF. Sanctum APIs offer open, list, messages, poll after cursor, send, mark read and closed/open status change.
- New messages checked every 15 seconds while browser tab is visible. Uses normal shared-hosting PHP requests rather than a dedicated WebSocket service.
- Per-user read cursors, unread counts, close/reopen events and XSS-safe escaped messages. Privileged owner/admin can close conversation; applicant cannot.
- CSP denies third-party scripts and framing, and Cache-Control is private/no-store. Rate limits control sending and polling.
- APPLICATION_CHAT_ENABLED defaults to false; navigation stays hidden until staging activation.

## Explicit limitations
- This is job-application messaging, not anonymous stranger chat or a complete public live-chat SaaS.
- Attachments are disabled until malware scanning, secure file review and retention have been implemented.
- Polling is near-real-time, not instant socket push. No native push notification, typing indicator, voice/video or message attachments.
- Decrypted message history depends on protecting the Laravel APP_KEY and encrypted backups. Rotating APP_KEY without migration makes old ciphertext unreadable.
- Only the most recent 40 messages render in the first thread view; pagination for older history remains needed.
- Retention/privacy notice, DSAR/export, deletion schedule, abuse reporting and backup policies need approval before handling real client conversations.

## Staging release gates
1. Verify isolated staging backup and independently review additive migrations 2026_10_22_000012 and 2026_10_22_000013.
2. Apply migrations only on staging, never production. Grant manage-application-chat only to approved staff.
3. Set APPLICATION_CHAT_ENABLED=true only in staging; clear Laravel config cache.
4. Verify applicant A, employer A, applicant B, employer B, admins without permission and guests cannot cross thread boundaries, via browser and Sanctum.
5. Test read/unread updates, closed status, multiple browsers, multilingual content, XSS payloads and session/CSRF expiry.
6. Load test polling for Hostinger cPanel worker and inode limits. Implement retention/abuse handling before enabling real clients.
7. Keep PR Draft until independent staging checks and explicit production approval.

Previous OTP, SMTP Email Studio, passport management and interview modules are unchanged by this change.
