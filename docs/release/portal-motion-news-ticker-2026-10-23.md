# Approved portal motion and news ticker

Owner requested site-wide animation and a news strip matching the navy/yellow header strip at eagleglobalhub.com, with configurable speed.

- Public portal, authentication cards, job cards, tracker panels and dashboard receive short entrance/reveal and hover transitions. Content is visible by default and remains usable without JavaScript.
- Reduced-motion preference disables decorative animation and shows a static, wrapping news strip. No blinking or perpetual decorative hero motion.
- Ticker speed is saved as a bounded 15–160 velocity value; duration is derived from measured headline width, so changing text length does not change scrolling speed.
- Admin sidebar and General Settings link to News Ticker. Existing application-setting permission plus administrator user type are required. Up to ten escaped headlines, optional safe site/HTTPS links, show/hide and a Slow/Fast slider are editable.
- The public ticker has Pause/Play and pauses for mouse hover, link focus, touch and hidden tabs. Duplicated content is hidden from screen readers and the keyboard tab order. No JavaScript leaves all original headlines readable.
- One additive InnoDB settings table brings migration count from 60 to 61. Default headlines describe existing portal behavior rather than fabricated news or statistics.
- No third-party chat embed, original design replacement, account permission grant or customer data rewrite is part of this release.

## Verified release

CI run 39901954107, PR #12 head 6c63cff248773a6b6830e4bb3cca7c0d9ea7d97f: all four jobs passed; 153 tests / 1,083 assertions. The exact tested PR artifact was used.

Production run 39902921382 succeeded. Deployment ID 20261023-045625-229f7d93; migration count 61. Encrypted backup 20261023-044741-052f28c8 was retained within its 15-minute gate after checking archive hashes, original APP_KEY and exact current source-file fingerprint.

Hosting quota blocked preparation. Only identical immutable dependency/build files were shared; no customer upload, environment, database record or prior backup was removed. The verified candidate tree was linked into private preparation, retaining the independent compressed artifact and previous production code.

Browser confirmed the live navy/yellow headline strip, saved speed 60, hero jg-rise animation, stable ticker transform while paused and advancing transform after Play. Job-card hover reached translateY(-5px) after entrance completion. Admin permissions, URL restrictions, escaping, speed bounds and visibility persistence were covered by regression tests. Reduced-motion fallback is implemented; no browser preference was changed during QA. Workflow restored to manual-only after success.
