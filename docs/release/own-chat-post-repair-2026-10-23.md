# First-party chat and job post repair

The owner explicitly requires the site's own chatbox. Do not restore Tawk or another third-party chat embed during layout changes. Private application messaging remains separate from the public visitor support widget.

## Current behavior

- The visitor widget uses same-origin session/CSRF protection and encrypted contact/message storage. A thread UUID alone never grants access: the visitor must own the matching session.
- Offline visitors can leave their name, email and message. This captures the email privately; it does not claim an email was sent.
- Authorized administrators use **Visitor Chat Inbox** in the admin sidebar to read/reply, show/hide the widget and set availability. Availability expires after 90 seconds unless the visible, opted-in inbox refreshes it. Existing manage-application-chat permission is required.
- Public job details preserve safe paragraphs, numbered/bulleted lists, workplace/job-level labels, readable benefits, currency and job summary. Employment type IDs resolve through the existing job type catalog.
- Rich-text rendering uses a DOM allowlist. Scripts, event handlers, unsafe URL schemes and active embeds are removed. Known poster email addresses remain hidden unless the administrator enables publication.
- Job editor/API values keep their original rich-text and JSON formats. Email redaction alone modifies serialization while the setting is hidden, including encoded addresses. Original database content is never rewritten for presentation.
- Presence, conversation creation, message polling, sending and admin actions have separate rate-limit keys.

## Preservation

The additive chat migration creates three InnoDB tables and brings the migration count to 60. Existing users, jobs, application data, uploads and APP_KEY remain intact. Subsequent display/rate-limit fixes require no new migration. CI, private staging, verified encrypted backup and live HTTP checks precede promotion.

## Verified release

CI run 39900543696, source 91ec885d4e6f3307981911ce06b6353a6a953407: all four jobs passed; 149 tests / 1,040 assertions, production/frontend dependency audits clear.

Production run 39900765513 succeeded. Deployment ID: 20261023-043256-287338ce. Fresh encrypted backup: 20261023-043225-2eb35092; decrypt/hash verification passed. Migration count remains 60. Original rows, uploads and APP_KEY preserved; previous code retained privately.

Browser verified real post /vacancies/38: numbered responsibility list, readable benefits, GBP currency and employment label Full Time. Native visitor chat opens with offline name/email/message form; no Tawk script is present. No real visitor enquiry or admin reply was submitted during browser QA. Session isolation, encrypted storage, admin replies and visibility/presence controls passed automated tests.

Visitor inbox requires the existing manage-application-chat permission. No existing role was silently granted private conversation access. Deployment workflow restored to manual-only.
