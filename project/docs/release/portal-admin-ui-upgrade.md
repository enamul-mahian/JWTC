# Portal administration and UI release

Deployed 2026-10-23; deployment 20261023-132510-865dc136. Encrypted backup 20261023-132437-f11ec03c verified before promotion. Original records, uploads, environment and application key preserved; 63 migration records.

Candidate f2742c9bd94523fe9e64c0d1169668aba60f8f7b passed CI 39960218126, including 170 PHP tests, compiled Blade syntax, JavaScript checks, frontend build and MySQL/MariaDB preservation checks.

Production read-only rendering passed admin job list/editor, workflow settings, application list/progress, chat inbox/schedule and approved employer job creation. No customer writes occurred in these checks. Authenticated browser visual testing was not performed.

Admin menu: Chat Inbox & Schedule handles visitor messages, replies, weekly hours and timezone. Availability also requires an operator heartbeat; offline messages remain accepted. Applications & Progress shares the approved application tracker; Workflow Settings edits stages. Manage All Jobs edits company jobs while preserving ownership and URLs.

Native job forms and scoped legacy form compatibility remove the missing Form class failure. Standalone error views avoid recursive layout failures. Registration primary button now uses explicit blue/white styles. Public browser verified RGB(7,88,223), white text, 14px Arial weight 650 and opacity 1. UK flag/Westminster watermark added. Modern routes omit unused legacy asset bundles. Mobile OS installation was not tested.

Deployment run 39960824462 completed successfully. One-shot deployment and diagnostics push triggers restored to manual dispatch.
