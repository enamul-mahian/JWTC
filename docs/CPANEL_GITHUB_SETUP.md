# GitHub → cPanel configuration

This first workflow checks SSH and the Laravel host without changing application files or the database. It does not deploy code. Production rollout still requires current backup/restore evidence, restored-data staging tests, mail/PDF/browser checks and an approved release commit. The owner has requested production completion; the remaining blocker is operational access and evidence, not another requirements approval.

## 1. Dedicated deploy identity

In cPanel → Security → SSH Access → Manage SSH Keys, generate a dedicated key named `jwtc_github_actions`, then **Authorize** its public key. Use a dedicated automation key with no passphrase for this workflow. Download its OpenSSH private key and put it directly in GitHub's encrypted secret field. Never commit it or paste it in chat. Do not reuse a personal key or overwrite the existing repository key.

Use the SSH hostname/IP and port supplied by the hosting provider, not a guessed cPanel web-panel port. This workflow accepts DNS names and IPv4 hosts. If the provider blocks GitHub-hosted runner IPs, arrange a supported connection with the provider before proceeding.

## 2. GitHub production environment

Repository → Settings → Environments → New environment → `production`. Add a required reviewer if your GitHub plan supports it, and restrict deployment branches to `main`. Under that environment's secrets add:

| Secret | Value |
|---|---|
| `CPANEL_SSH_HOST` | Hosting SSH hostname or IPv4 address |
| `CPANEL_SSH_PORT` | Provider-confirmed SSH port |
| `CPANEL_SSH_USER` | cPanel SSH username |
| `CPANEL_SSH_PRIVATE_KEY` | Complete dedicated OpenSSH private key, including header/footer |
| `CPANEL_SSH_KNOWN_HOSTS` | Verified OpenSSH known_hosts entry for this host/port |
| `CPANEL_APP_PATH` | Confirmed Laravel root containing artisan and .env; previously reported `/home/dropoqux/joingallagher.uk`, verify before use |

If environment secrets are unavailable, use repository → Settings → Secrets and variables → Actions → New repository secret. Never store these values as ordinary source files. This connector cannot inspect or set your GitHub secrets.

## 3. Verify server identity

Obtain the server SSH host-key fingerprint independently from the provider. From your own trusted terminal, collect its known_hosts line with `ssh-keyscan -p PORT HOST > jwtc_known_hosts`, inspect fingerprints with `ssh-keygen -lf jwtc_known_hosts`, and compare with the provider's fingerprint before saving the file contents as `CPANEL_SSH_KNOWN_HOSTS`. Replace HOST and PORT first. Do not accept unverified scan output. A nonstandard port uses `[HOST]:PORT` in the entry. Never disable StrictHostKeyChecking.

## 4. Run

After this workflow-only change is merged into main: Actions → **cPanel SSH Preflight** → Run workflow → select `main`. Missing configuration or failed SSH returns a failed run; it does not alter live files. Share only the run URL, not credentials or applicant data.

The workflow validates PHP CLI 8.2+, required extensions, availability of composer/rsync/tar/gzip and existing Laravel writable directories. It deliberately does not bootstrap the live Laravel application or print .env, database rows, upload names, private keys or credentials. Missing Poppler tools for Email Studio PDF rendering, queue/cron settings, DNS/mail and storage mappings require separate validation.

## 5. Remaining production work

Do not turn the current draft feature PR into an automatic push-to-main deployment. First retain consistent current SQL and full public/private uploads backups with original APP_KEY escrow, restore in an independent database/root, compare rows/IDs/files, and complete staging acceptance checks documented in STAGING_INTEGRATION_PRECHECK.md. Confirm the actual public document root, shared storage/symlinks and PHP executable before implementing the upload/cutover workflow. Preserve existing .env, APP_KEY, uploads, analytics and SEO. Never use rsync --delete against an unreviewed live tree or migrate:fresh/refresh/reset/db:wipe. Code rollback must preserve new records and must not run migration down().

Once these operational checks pass, the release workflow can build the exact tested commit, transfer a versioned package, run reviewed additive migrations and switch code with a rehearsed rollback. A green preflight alone is not a production release.
