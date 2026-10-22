<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Strictly read-only. Does not backup, migrate, create files, change config,
 * restore databases, alter permissions or enable any production modules.
 */
class StagingReleasePreflight extends Command
{
    protected $signature = 'jwtc:staging-preflight
        {--expected-db= : Exact isolated staging MySQL database name}
        {--production-db= : Exact production database name (must differ)}
        {--expected-url= : Exact staging application URL}
        {--expected-key-sha256= : SHA-256 fingerprint from independently escrowed original APP_KEY}
        {--production-root= : Existing on-disk live application root to protect}
        {--uploads=* : Repeat for each staging-relative upload/storage folder to verify}';

    protected $description =
        'Read-only staging identity, data, upload, key, feature-schema and asset checks; never deploys';

    private array $issues = [];

    private function check(bool $ok, string $name, string $failure): void
    {
        if ($ok) {
            $this->line('[PASS] ' . $name);
        } else {
            $this->line('[FAIL] ' . $name . ': ' . $failure);
            $this->issues[] = $name;
        }
    }

    public function handle(): int
    {
        $this->info('JWTC Staging Safety Preflight (READ ONLY)');
        $this->line('No migration, deletion, database update or filesystem write is executed.');

        $env = (string) config('app.env');
        // Refuse "local", "testing" and especially "production", even with
        // an explicit operator-supplied database name.
        $this->check($env === 'staging', 'Application environment',
            'APP_ENV must be exactly staging.');
        if ($env !== 'staging') return self::FAILURE;

        $expected = trim((string) $this->option('expected-db'));
        $production = trim((string) $this->option('production-db'));
        $url = rtrim(trim((string) $this->option('expected-url')), '/');
        $escrowedKeyHash = strtolower(trim((string) $this->option('expected-key-sha256')));
        $liveRoot = trim((string) $this->option('production-root'));
        $paths = (array) $this->option('uploads');

        $this->check(
            $expected !== '' && $production !== '' && $expected !== $production,
            'Database isolation inputs',
            'Supply different, nonempty --expected-db and --production-db values.'
        );
        $this->check(
            str_starts_with($url, 'https://') &&
                rtrim((string) config('app.url'), '/') === $url,
            'Staging URL identity',
            'An HTTPS --expected-url must match APP_URL exactly.'
        );
        $base = realpath(base_path());
        $live = $liveRoot !== '' ? realpath($liveRoot) : false;
        $this->check(
            $base !== false && $live !== false && $base !== $live,
            'Application root separation',
            'Supply an existing --production-root distinct from staging.'
        );
        $this->check(!config('app.debug'), 'Debug mode',
            'APP_DEBUG must be false when checking a hosted staging release.');

        // Never emit APP_KEY or mail/payment/DB credentials in console output.
        try {
            $plain = 'preflight-only-test-token';
            $encrypted = Crypt::encryptString($plain);
            $keyValid = Crypt::decryptString($encrypted) === $plain;
        } catch (Throwable $error) {
            $keyValid = false;
        }
        $this->check($keyValid, 'APP_KEY encryption/decryption',
            'Cannot encrypt/decrypt private data with the current APP_KEY.');
        $currentKeyHash = hash('sha256', (string) config('app.key'));
        $this->check(
            preg_match('/^[a-f0-9]{64}$/', $escrowedKeyHash) === 1 &&
                hash_equals($escrowedKeyHash, $currentKeyHash),
            'Escrowed APP_KEY identity',
            'Staging key does not match the independently retained original key fingerprint.'
        );

        $this->check(
            count($paths) >= 2 &&
                in_array('storage/app', $paths, true),
            'Upload coverage inputs',
            'Supply --uploads=storage/app and all relevant public upload folders.'
        );
        foreach ($paths as $path) {
            $path = (string) $path;
            // Only check staging-relative directories. Do not dereference or
            // accidentally read the live site's private files.
            $valid = $path !== '' && !str_starts_with($path, '/') &&
                !str_contains($path, '\\') &&
                !in_array('..', explode('/', $path), true) &&
                !in_array('', explode('/', $path), true);
            $resolved = $valid ? realpath(base_path($path)) : false;
            $inside = $resolved !== false && $base !== false &&
                str_starts_with($resolved . DIRECTORY_SEPARATOR,
                    $base . DIRECTORY_SEPARATOR);
            $this->check(
                $valid && $inside && is_dir($resolved) && is_readable($resolved),
                'Upload directory: ' . preg_replace('/[^a-zA-Z0-9_\/.-]/', '_', $path),
                'Directory missing, inaccessible, outside staging or unsafe relative path.'
            );
        }

        // Do not contact a database until environment, paths and names are
        // confirmed to be separate from production.
        if ($this->issues) {
            $this->error('BLOCKED: Staging identity/key/storage checks failed. DB not queried.');
            return self::FAILURE;
        }

        $this->check(
            config('database.default') === 'mysql' &&
                config('database.connections.mysql.driver') === 'mysql',
            'Database driver', 'Staging must use independent MySQL.'
        );
        if ($this->issues) return self::FAILURE;

        try {
            // SELECT only; compare real connected DB, not just .env variables.
            $selected = DB::connection('mysql')->selectOne('SELECT DATABASE() AS db');
            $actual = (string) ($selected->db ?? '');
            $this->check($actual === $expected && $actual !== $production,
                'Connected database identity',
                'The active MySQL schema does not match isolated staging.');
            if ($this->issues) return self::FAILURE;

            foreach (['migrations', 'users', 'jobs', 'applications'] as $table) {
                $this->check(Schema::connection('mysql')->hasTable($table),
                    'Existing table: ' . $table,
                    'Missing restored application table.');
            }
            if ($this->issues) return self::FAILURE;

            $migrated = DB::connection('mysql')->table('migrations')->count() > 0;
            $this->check($migrated, 'Existing migration history',
                'Empty migration repository: schema may be unrecoverable.');

            foreach ([
                'application_chat.enabled' => ['application_chat_threads',
                    'application_chat_messages', 'application_chat_read_states'],
                'support_center.enabled' => ['support_tickets',
                    'support_refund_requests', 'support_refund_events'],
                'email_studio.enabled' => ['email_studio_templates',
                    'email_studio_send_logs', 'email_studio_smtp_profiles'],
            ] as $flag => $tables) {
                if (!config($flag)) continue;
                foreach ($tables as $table) {
                    $this->check(Schema::connection('mysql')->hasTable($table),
                        'Enabled feature table: ' . $table,
                        'Feature enabled before additive staging migration.');
                }
            }
        } catch (Throwable $error) {
            // No raw error: PDO exceptions can include credentials/connection URLs.
            $this->check(false, 'Read-only MySQL inspection',
                'Connection or schema inspection failed; review protected server logs.');
        }

        if (config('jwtc_design.enabled')) {
            foreach (['theme/jwtc.css', 'build/manifest.json'] as $relative) {
                $this->check(is_file(public_path($relative)),
                    'Redesign asset: ' . $relative, 'Staging asset is absent.');
            }
        }

        if ($this->issues) {
            $this->error('BLOCKED: ' . count($this->issues) . ' release-preflight check(s) failed.');
            return self::FAILURE;
        }

        $this->info('PASS: Read-only staging preflight complete.');
        $this->warn('NOT PROOF OF BACKUP/RESTORE OR PRODUCTION RELEASE APPROVAL.');
        $this->line('Also verify encrypted offsite DB + upload backups, actual isolated');
        $this->line('restore, data/file hashes, user flows and explicit cutover approval.');
        return self::SUCCESS;
    }
}
