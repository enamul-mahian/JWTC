# Production deployment — 22 October 2026 UTC

Live promotion succeeded: https://github.com/enamul-mahian/JWTC/actions/runs/39865495143

- Deployment: `20261022-223158-07e333f2`, completed 22:32:17 UTC (23 October, 04:32 Bangladesh).
- Application candidate source: `5965a05766cfbd1277d644fa5e8de74a6f6af982`; verified artifact from CI run `39865140624`.
- Laravel 12.70.1, hosting PHP 8.3.35, Node 24 frontend build, Vite 7.3.7, supported Tailwind/PostCSS integration.
- CI: 136 tests / 913 assertions, temporary MySQL schema, historical MariaDB migration/data preservation, production-only packaging. Composer production audit and complete npm audit reported zero advisories/vulnerabilities.
- Actual restored staging data and files passed the runtime upgrade and HTTP smoke gate.
- Fresh encrypted production backup `20261022-223126-eff25190`; encrypted/plaintext SHA256 checked, original APP_KEY escrowed separately. Earlier backup download was owner-reported; off-host download of this new backup has not been verified.
- Production: 15 additive migrations, second migration invocation idempotent, 58 migration records. Every preexisting row in 46 tables was verified unchanged; only migration/permission rows were allowed to be added. Retained uploads/files were hash checked. Existing environment values were preserved except APP_ENV=production and APP_DEBUG=false; original key remained unchanged.
- Live HTTPS smoke: `/`, `/login`, `/register`, `/contact` returned 200; unauthenticated `/admin/dashboard` returned 302.
- Previous code/environment/cache retained privately under `/home/dropoqux/jwtc-private-deployments/20261022-223158-07e333f2/`. Promotion script includes code restoration without destructive database rollback on failure. No failure rollback was needed in this deployment.

The deployment workflow is manual-only after this successful one-shot authorized promotion. The current script deliberately requires the pre-upgrade migration baseline, so it must be reviewed for a subsequent release. Do not rerun it blindly.

This proves this release was deployed and these checks passed. It does not certify every product requirement, authenticated browser flow, real SMTP delivery, or future third-party service behavior. Feature settings and sensitive permissions were not automatically enabled/granted. Node is used on the build runner; this PHP application does not need a new cPanel Node runtime to serve the built assets.
