# Approved portal motion and news ticker

Owner requested site-wide animation and a news strip matching the navy/yellow header strip at eagleglobalhub.com, with configurable speed.

- Public portal, authentication cards, job cards, tracker panels and dashboard receive short entrance/reveal and hover transitions. Content is visible by default and remains usable without JavaScript.
- Reduced-motion preference disables decorative animation and shows a static, wrapping news strip. No blinking or perpetual decorative hero motion.
- Ticker speed is saved as a bounded 15–160 velocity value; duration is derived from measured headline width, so changing text length does not change scrolling speed.
- Admin sidebar and General Settings link to News Ticker. Existing application-setting permission plus administrator user type are required. Up to ten escaped headlines, optional safe site/HTTPS links, show/hide and a Slow/Fast slider are editable.
- The public ticker has Pause/Play and pauses for mouse hover, link focus, touch and hidden tabs. Duplicated content is hidden from screen readers and the keyboard tab order. No JavaScript leaves all original headlines readable.
- One additive InnoDB settings table brings migration count from 60 to 61. Default headlines describe existing portal behavior rather than fabricated news or statistics.
- No third-party chat embed, original design replacement, account permission grant or customer data rewrite is part of this release.
