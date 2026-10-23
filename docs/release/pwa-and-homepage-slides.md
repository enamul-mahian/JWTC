# Mobile installation, homepage slides and alignment

Administrator sign-in: `/admin/login`. A visible Admin login link is now in the website footer. The existing `application-setting` permission controls the Homepage Slides editor at `/admin/hero-slides`; no role permissions are automatically expanded.

In Homepage Slides, upload JPG, PNG or WebP images, set display order, choose 5–30 seconds between slides, or disable autoplay. Keep at least one image and at most five. Uploaded images are decoded, resized and re-encoded to WebP with random filenames; SVG and executable uploads are rejected. Removed images are retained for recovery. Prefer wide landscape photographs. Images are stored under `public/uploads/hero`, excluded from release packages and preserved during deployment.

Mobile visitors receive a dismissible install suggestion. Supported Chromium browsers can show their native installation dialog after tapping Install app. Other browsers display instructions; iPhone/iPad users use the Share menu and Add to Home Screen. The footer Install app button reopens the instructions. Installed standalone windows hide the prompt; dismissals suppress automatic suggestions for seven days. Native eligibility and installation remain controlled by the browser.

The service worker caches only a generic offline document. Account pages, applications, chat messages, API responses and form submissions are never stored by this worker. Offline navigation shows the generic reconnect page rather than personal content. No push notification permission is requested.

Header, ticker, statistics and job listings use the same 1200px container and matching side padding. Job cards reserve matching title/metadata space on desktop and align their footer links; smaller screens use a single column and flexible headings/footer links. The hero supports manual previous/next buttons and pause/play, pauses while hovered/focused or hidden, and respects reduced-motion preferences.

## Background asset provenance

Original backgrounds were created with the built-in ImageGen tool, then converted to optimized WebP for this project. The Wikimedia image and its associated attribution line are both removed from the active design. Generated files are not third-party photographs.

Saved assets:
- `public/theme/hero-london.webp`
- `public/theme/hero-tower-bridge.webp`

Prompt 1: “Use case: photorealistic-natural. Asset type: wide background photograph for a UK job portal homepage. Create an original cinematic yet natural London view: Big Ben and Palace of Westminster across the River Thames at blue hour just after sunset, warmly lit windows, deep navy sky, subtle amber horizon, calm reflections. Panoramic 3:1 landscape composition with buildings towards the right and darker calm negative space on the left for white website headings. No text, logos, watermark, frames or interface. Deliver a single realistic photograph-style image.”

Prompt 2: “Use case: photorealistic-natural. Asset type: original panoramic 3:1 background photo for UK careers website slideshow. A realistic beautiful London skyline along the Thames at dawn, Tower Bridge towards the right of the composition, city towers distant, soft blue and gold sky, natural calm water. Wide horizontal composition with dark blue calm usable negative space on the left where white website headings will overlay. Match premium navy-blue UK recruitment portal visual style, natural photographic texture. No people close up, no text, watermark, logos, frames or interface.”

## Validation

CI run 39949558253 passed all four jobs: 157 PHP tests / 1130 assertions, service-worker privacy tests, PHP/Blade checks, frontend build and dependency audits, MySQL compatibility, and MariaDB preservation checks with 62 migrations. Feature tests cover administrator permissions, image re-encoding, reordering, disabling autoplay, invalid uploads/settings, public markup and icon dimensions. No live customer records were used in CI.

A staging rehearsal preserves original records, uploads, environment and encryption key before production promotion. Production browser acceptance is recorded separately; actual mobile OS installation requires an eligible browser and the visitor's installation action.

Production deployment: `20261023-115914-663687c8`, Actions run `39949801545`; encrypted backup `20261023-115835-ea1dd3f0` verified by decryption and hashes. Public login/auth routes and all PWA/slideshow assets passed HTTP checks. Browser verification found the header, ticker, statistics and job list all at x=74px and width=1200px in the 1348px viewport; the first card row had equal heights and matching footer baselines. Both generated slide images loaded, previous/next and pause/play controls worked, and the footer install button displayed installation guidance. The Wikimedia credit is absent. Physical mobile OS installation and a signed-in production admin upload were not exercised; permission/upload behaviour is covered by isolated feature tests.
