# JWTC SMTP Email Studio — Gmail-friendly Visual Builder

**Status:** Implemented in Draft PR #3 on the development branch. **Not merged or deployed.** Existing OTP, interview notification and Mass Mail controllers remain separate and unchanged.

## Designed to match the earlier WorkForNZ Visual Email Studio

- A standalone dashboard entry, **SMTP Email Studio**, restricted to admin/super-admin with an explicitly granted `manage-email-studio` permission.
- **Legacy NZ HTML template import:** Select a local `.html`/`.htm` file (up to 1 MB), then replace or append its content in the visual editor. The import happens in the browser, with a strict tag/style/layout allowlist; the Laravel server sanitizes everything again when saved. Safe email table widths, padding, alignment and text styles can be retained. Remote images, scripts, embedded tracking assets and unsupported CSS are removed, so import is **not** pixel-identical to the original. Images must be uploaded through the three approved private image slots.
- **PDF → visual email pages:** A saved template can import a PDF with 1–3 pages and render it to width-limited PNG images using `pdfinfo` + `pdftoppm` (Poppler) on the hosting server; alternatively upload reviewed PNG/JPG pages without the binaries. The pages are stored privately and displayed as ordered CID inline images in the Gmail HTML. This is independent of optional PDF attachment and generated A4 PDF. **PDF tools and malware/security inspection must be approved and tested on staging before using real applicant PDFs.**
- The screenshot-inspired **single Studio dashboard** presents (1) Template Builder, (2) Live Preview, (3) Send Email, (4) Custom SMTP Manager, (5) Recent Email Attempts. Template Duplicate/Delete preserve audit logs; duplicates copy private image files rather than sharing paths.
- Create/edit/save named templates. WYSIWYG contenteditable editing with bold, italic, underline, strike, font family, font size, text color, bullet/number lists and alignment.
- Formatting from pasted text is preserved for supported safe CSS properties. A server-side DOM allowlist removes dangerous tags, JavaScript, remote image embeds, unsupported CSS, event handlers and unsafe links.
- Live desktop/mobile sandboxed iframe preview. Exact Gmail/Outlook pixel matching and freeform X/Y positioning are **not promised**; email engines cannot reliably render absolute-positioned layouts.
- One top banner and two bottom image slots, with width controls and drag/drop file selection. Uploaded images are validated, private, and embedded as **CID inline email attachments** when the SMTP message is sent.
- Table-based (600 px responsive) inline-style HTML plus a plain-text alternative for Gmail and other mail clients.
- Safe variables: `{{client_name}}`, `{{reference}}`, `{{status}}`, `{{message}}`, `{{date}}` in body/subject.
- Send a **single transactional email** through either dedicated `studio_smtp` or an approved encrypted custom SMTP profile. Custom profile passwords use Laravel encryption based on the server's APP_KEY, are never sent back to the browser, and leaving password blank during edit retains it. The profile manager supports Edit, Test, Set Default and Delete; testing sends to the logged-in admin's own email. SMTP connection errors are not shown verbatim to the browser. Send forms accept an existing PDF attachment (maximum 8 MB), or generate an A4 PDF from the sanitized template with DomPDF.
- Logs record sender admin ID, recipient, subject, SMTP accepted/failed result and PDF presence. They never include SMTP password, attached PDF bytes or pasted applicant documents.
- Server endpoint allows only `5 sends/min` and `25 sends/hour`, with Laravel CSRF protections. The module is disabled by default.
- No third-party scripts in the visual editor. HTTP responses are no-store, no-referrer and deny framing.

## Private Hostinger environment

Do **not** commit credentials, publish the .env file, or store SMTP passwords in editable email templates. Use staging server-side environment configuration (example only):

```dotenv
EMAIL_STUDIO_ENABLED=true
EMAIL_STUDIO_SMTP_HOST=<your authorised SMTP server>
EMAIL_STUDIO_SMTP_PORT=587
EMAIL_STUDIO_SMTP_ENCRYPTION=tls
EMAIL_STUDIO_SMTP_USERNAME=<approved SMTP mailbox>
EMAIL_STUDIO_SMTP_PASSWORD=<private password or SMTP app password>
EMAIL_STUDIO_FROM_ADDRESS=<authenticated domain address>
EMAIL_STUDIO_FROM_NAME="Join Gallagher"
```

The Studio uses the dedicated `studio_smtp` Laravel mailer and its `EMAIL_STUDIO_SMTP_*` environment variables. When those variables are absent, the transport falls back to your existing `MAIL_*` settings for backwards compatibility, without changing them. Dedicated Studio sender name/address are separate. The built-in legacy general Email Settings page is separate. Before launch, audit any legacy code that writes plaintext mail credentials to database or directly rewrites `.env`; do not expose credentials via dashboards or application logs.

After staging-only schema migration and SMTP credentials, grant `manage-email-studio` to explicitly approved admin accounts through authorized RBAC. **Do not grant send rights to every admin by default.**

## Gmail compatibility and deliverability checks

1. Verify SPF, DKIM, DMARC domain authentication, aligned From domain, valid forward/reverse DNS, TLS, and appropriate sender reputation using official Gmail sender guidance: https://support.google.com/mail/answer/81126
2. Send a test to a designated Gmail inbox; use Gmail's **Show original** to verify SPF/DKIM/DMARC authentication and check the HTML/inline image layout plus optional attached PDF.
3. Check Gmail web and mobile rendering for font fallback, images shown and hidden, image widths, links, paragraphs, non-Latin text, and dark mode. Some clients rewrite HTML.
4. Verify PDF attachment can be downloaded, is legible, and matches intended document content; document generation has no external network resource access.
5. Protect against unsolicited email. This module is for deliberately addressed **transactional** messages, not automated bulk marketing. Marketing/subscribed messages require consent, unsubscribe mechanism, and applicable legal requirements.
6. Confirm SMTP accept is not misrepresented as final mailbox delivery. Investigate bounced and spam-filtered messages separately.
7. After successful staging testing, approve a separate production release; **never copy actual applicant attachments to a public location or GitHub.**

## Staging release gates

- Review additive migrations `2026_10_22_000008_create_email_studio_tables.php`, `2026_10_22_000009_create_email_studio_permission.php`, `2026_10_22_000010_create_email_studio_pdf_visual_pages.php`, and `2026_10_22_000011_create_email_studio_smtp_profiles.php` on an independent staging database after backup.
- Confirm PHP DOM, GD, DomPDF, SMTP, `storage/app` permissions and outbound SMTP connectivity on the hosting account. For PDF-to-email, install and review Poppler `pdfinfo` / `pdftoppm` or use manual PNG/JPG uploads. Run rasterization only on an isolated staging host with memory/time/file limits and malicious-PDF scanning.
- Grant only named staff the new permission; test other admins, applicant and employer access are forbidden.
- Verify safe HTML paste behavior with image/font alignment; test live preview, CID images and PDF upload/generated attachment using a real Gmail account.
- Validate SMTP failure behavior, logging, invalid email, rate limiting, PDF size/type checks and responsive mobile UI.
- Confirm existing Mass Mail, password OTP and interview notifications retain their original mailer behavior.
- Keep PR #3 Draft until additional modules and real staging integration are approved.

**Current limitations:** No Gmail API or OAuth credential manager, mass marketing campaigns, automatic retry or bounce processing, native rasterizer sandbox, or perfect X/Y pixel reproduction across mail clients. Saved SMTP credentials are encrypted but their security depends on protecting the Laravel APP_KEY and on correct administrative RBAC. Importing arbitrary PDF without sandbox/malware scanning is not approved for production. The exact WorkForNZ v4 ZIP implementation has not been independently verified; the provided screenshot is the layout reference. No actual DNS changes or real SMTP delivery/inbox test was performed.
