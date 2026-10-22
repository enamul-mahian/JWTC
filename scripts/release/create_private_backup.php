<?php

// Read-only backup-planning inventory. Never bootstrap Laravel, run migrations,
// create accounts, change .env, or print connection values/private data.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
set_error_handler(static function () { throw new RuntimeException('Read failed'); });

$backupPhase = 'inventory';
try {
    $input = json_decode(stream_get_contents(STDIN), true, 8, JSON_THROW_ON_ERROR);
    if (!is_array($input) || ($input['database'] ?? '') !== 'dropoqux_jwtc_stage'
        || ($input['username'] ?? '') !== 'dropoqux_jwtc_stage'
        || !is_string($input['password'] ?? null) || $input['password'] === '') {
        throw new RuntimeException('Unexpected staging identity');
    }
    if (!is_file('artisan') || !is_file('.env') || !is_file('vendor/autoload.php')) {
        throw new RuntimeException('Laravel root or dependencies unavailable');
    }
    require 'vendor/autoload.php';
    if (is_file('bootstrap/cache/config.php')) {
        $config = require 'bootstrap/cache/config.php';
        $driver = $config['database']['default'] ?? '';
        $db = $config['database']['connections'][$driver] ?? [];
        // URL connections need independent resolution; do not guess their DB.
        if ($driver !== 'mysql' || !empty($db['url'])) {
            throw new RuntimeException('Unsupported cached database configuration');
        }
        $liveDatabase = $db['database'] ?? '';
        $liveUsername = $db['username'] ?? '';
        $livePassword = $db['password'] ?? '';
        $host = $db['host'] ?? '127.0.0.1';
        $port = $db['port'] ?? '3306';
        $socket = $db['unix_socket'] ?? '';
        $ssl = $db['options'] ?? [];
    } else {
        $env = Dotenv\Dotenv::parse(file_get_contents('.env'));
        $value = static function ($key, $fallback = '') use ($env) {
            $process = getenv($key);
            return $process !== false ? $process : ($env[$key] ?? $fallback);
        };
        if ($value('DB_CONNECTION', 'mysql') !== 'mysql' || $value('DATABASE_URL') !== ''
            || $value('DB_URL') !== '') {
            throw new RuntimeException('Unsupported database configuration');
        }
        $liveDatabase = $value('DB_DATABASE');
        $liveUsername = $value('DB_USERNAME');
        $livePassword = $value('DB_PASSWORD');
        $host = $value('DB_HOST', '127.0.0.1');
        $port = $value('DB_PORT', '3306');
        $socket = $value('DB_SOCKET');
        $ssl = [];
        if ($value('MYSQL_ATTR_SSL_CA') !== '') {
            $ssl[PDO::MYSQL_ATTR_SSL_CA] = $value('MYSQL_ATTR_SSL_CA');
        }
    }
    if (!is_string($liveDatabase) || $liveDatabase === ''
        || $liveDatabase === $input['database'] || str_contains($liveDatabase, '${')) {
        throw new RuntimeException('Cannot establish independent staging database');
    }
    if (!is_string($host) || !preg_match('/^[A-Za-z0-9_.:\\-]+$/D', $host)
        || !preg_match('/^[0-9]{1,5}$/D', (string) $port)
        || (int) $port < 1 || (int) $port > 65535
        || !is_string($socket) || str_contains($socket, ';')) {
        throw new RuntimeException('Unsupported database host');
    }
    $dsn = $socket !== '' ? 'mysql:unix_socket='.$socket : 'mysql:host='.$host.';port='.$port;
    $dsn .= ';dbname='.$input['database'].';charset=utf8mb4';
    // Preserve only SSL trust material; never inherit live credentials or a
    // configured init command that could execute arbitrary SQL on connection.
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_EMULATE_PREPARES => false];
    foreach ([PDO::MYSQL_ATTR_SSL_CA, PDO::MYSQL_ATTR_SSL_CERT, PDO::MYSQL_ATTR_SSL_KEY,
              PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] as $key) {
        if (array_key_exists($key, $ssl)) $options[$key] = $ssl[$key];
    }
    $pdo = new PDO($dsn, $input['username'], $input['password'], $options);
    $pdo->exec('SET SESSION TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    $actual = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if ($actual !== $input['database'] || $actual === $liveDatabase) {
        throw new RuntimeException('Database identity mismatch');
    }
    $query = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
    $query->execute([$actual]);
    $tables = (int) $query->fetchColumn();
    $pdo->rollBack();
    echo "PASS: staging credentials accepted; database differs from live configuration\n";
    if ($tables !== 0) {
        echo "STOP: staging database contains tables; no overwrite or restore attempted\n";
        exit(2);
    }
    echo "PASS: staging database is empty\n";
    if (!preg_match('/^[A-Za-z0-9_]+$/D', $liveDatabase)
        || !is_string($liveUsername) || !is_string($livePassword)) {
        throw new RuntimeException('Unsupported live identity');
    }
    $liveDsn = ($socket !== '' ? 'mysql:unix_socket='.$socket : 'mysql:host='.$host.';port='.$port)
        .';dbname='.$liveDatabase.';charset=utf8mb4';
    $live = new PDO($liveDsn, $liveUsername, $livePassword, $options);
    $live->exec('SET SESSION TRANSACTION READ ONLY');
    $live->beginTransaction();
    if ($live->query('SELECT DATABASE()')->fetchColumn() !== $liveDatabase) {
        throw new RuntimeException('Live identity mismatch');
    }
    $counts = [];
    $checks = [
        'base_tables' => "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE'",
        'non_innodb_tables' => "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE' AND (engine IS NULL OR UPPER(engine) <> 'INNODB')",
        'views' => "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_type = 'VIEW'",
        'triggers_visible' => 'SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema = ?',
        'routines_visible' => 'SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = ?',
        'events_visible' => 'SELECT COUNT(*) FROM information_schema.events WHERE event_schema = ?',
    ];
    foreach ($checks as $label => $sql) {
        $query = $live->prepare($sql);
        $query->execute([$liveDatabase]);
        $counts[$label] = (int) $query->fetchColumn();
    }
    $live->rollBack();
    if ($counts['base_tables'] === 0) {
        throw new RuntimeException('Unexpected empty live schema');
    }
    echo "PASS: live database metadata read using a read-only transaction\n";
    echo 'DATABASE_METADATA '.json_encode($counts, JSON_THROW_ON_ERROR)."\n";

    // Inventory only metadata. Never read/log applicant file contents or names.
    // Symlink targets outside the selected live application root need an
    // explicit storage mapping; do not silently omit them from future backups.
    $root = realpath('.');
    if (!$root || $root === '/' || $root === realpath(getenv('HOME') ?: '/')) {
        throw new RuntimeException('Unsafe application root');
    }
    $files = 0;
    $bytes = 0;
    $links = 0;
    $externalLinks = 0;
    $stack = [$root];
    while ($stack) {
        $directory = array_pop($stack);
        foreach (new DirectoryIterator($directory) as $entry) {
            if ($entry->isDot()) continue;
            $path = $entry->getPathname();
            if ($directory === $root && in_array($entry->getFilename(), ['.git', 'vendor', 'node_modules'], true)) {
                continue;
            }
            if ($entry->isLink()) {
                $links++;
                $target = realpath($path);
                if (!$target || ($target !== $root && !str_starts_with($target, $root.DIRECTORY_SEPARATOR))) {
                    $externalLinks++;
                }
                continue;
            }
            if ($entry->isDir()) {
                $stack[] = $path;
            } elseif ($entry->isFile()) {
                if (!$entry->isReadable()) throw new RuntimeException('Unreadable source file');
                $files++;
                $bytes += $entry->getSize();
            } else {
                throw new RuntimeException('Unsupported source entry');
            }
        }
    }
    $free = disk_free_space(dirname($root));
    echo 'FILESYSTEM_METADATA '.json_encode([
        'regular_files' => $files, 'regular_file_bytes' => $bytes,
        'symlinks' => $links, 'external_or_broken_symlinks' => $externalLinks,
        'parent_free_bytes' => $free === false ? null : (int) $free,
        'dependency_directories_excluded' => true,
    ], JSON_THROW_ON_ERROR)."\n";
    if ($externalLinks > 0) {
        echo "STOP: external/broken symlinks require explicit backup storage mapping\n";
        exit(2);
    }
    if ($counts['non_innodb_tables'] > 0) {
        echo "STOP: non-InnoDB tables require a reviewed consistent-snapshot procedure\n";
        exit(2);
    }
    echo "INFO: metadata visibility depends on DB privileges; zero visible routines/events is not proof of absence\n";

    // Create only private, unique backup outputs; never change source files.
    $run = static function (array $args, array $env = [], int $timeout = 120): string {
        $process = new Symfony\Component\Process\Process($args, null, $env);
        $process->setTimeout($timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException('Backup command failed; details suppressed');
        }
        return $process->getOutput();
    };
    $binary = static function (string $name): string {
        foreach (['/usr/bin/', '/bin/', '/usr/local/bin/', '/usr/local/cpanel/bin/'] as $directory) {
            if (is_executable($directory.$name)) return $directory.$name;
        }
        throw new RuntimeException('Required backup tool unavailable: '.$name);
    };
    $backupPhase = 'domain-root-check';
    $uapi = $binary('uapi');
    $backupPhase = 'uapi-domain-query';
    $domain = json_decode($run([$uapi, '--output=json',
        'DomainInfo', 'single_domain_data', 'domain=joingallagher.uk']), true, 64, JSON_THROW_ON_ERROR);
    if (($domain['result']['status'] ?? 0) !== 1) {
        throw new RuntimeException('Cannot establish domain document root');
    }
    $collectRoots = static function ($data) use (&$collectRoots): array {
        $roots = [];
        if (!is_array($data)) return $roots;
        foreach ($data as $key => $value) {
            if (in_array($key, ['documentroot', 'document_root'], true) && is_string($value)) {
                $resolved = realpath($value);
                if (!$resolved) throw new RuntimeException('Unresolved document root');
                $roots[] = $resolved;
            } elseif (is_array($value)) {
                $roots = array_merge($roots, $collectRoots($value));
            }
        }
        return array_values(array_unique($roots));
    };
    $backupPhase = 'domain-root-mapping';
    $domainRoots = $collectRoots($domain['result']['data'] ?? []);
    if (count($domainRoots) !== 1 || ($domainRoots[0] !== $root
        && !str_starts_with($domainRoots[0], $root.DIRECTORY_SEPARATOR))) {
        throw new RuntimeException('Separate public document root needs backup mapping');
    }
    $allDomains = json_decode($run([$uapi, '--output=json',
        'DomainInfo', 'domains_data', 'format=list']), true, 64, JSON_THROW_ON_ERROR);
    if (($allDomains['result']['status'] ?? 0) !== 1) {
        throw new RuntimeException('Cannot establish private backup destination');
    }
    $webRoots = $collectRoots($allDomains['result']['data'] ?? []);
    if (!$webRoots) throw new RuntimeException('No verified document roots');
    $backupPhase = 'account-quota-check';
    $quotaResponse = json_decode($run([$uapi, '--output=json',
        'Quota', 'get_quota_info']), true, 64, JSON_THROW_ON_ERROR);
    if (($quotaResponse['result']['status'] ?? 0) !== 1) {
        throw new RuntimeException('Cannot establish account disk quota');
    }
    $quota = $quotaResponse['result']['data'] ?? [];
    $limit = $quota['bytes_limit'] ?? ($quota['megabyte_limit'] ?? null);
    $remaining = $quota['bytes_remain'] ?? null;
    // cPanel versions may expose MB rather than bytes. Use decimal MB as a
    // conservative lower bound on available bytes (smaller than MiB).
    if ($remaining === null && is_numeric($quota['megabytes_remain'] ?? null)) {
        $remaining = (float) $quota['megabytes_remain'] * 1000000;
    }
    if ($remaining === null && is_string($quota['megabytes_remain'] ?? null)
        && in_array(strtolower($quota['megabytes_remain']), ['unlimited', 'unlim'], true)) {
        $limit = 'unlimited';
    }
    if ($remaining === null && is_numeric($limit) && is_numeric($quota['bytes_used'] ?? null)) {
        $remaining = (float) $limit - (float) $quota['bytes_used'];
    }
    $unlimited = is_string($limit) && in_array(strtolower($limit), ['unlimited', 'unlim'], true);
    // Observed cPanel unlimited representation: zero limit/remaining while
    // reporting existing usage and both quota-status flags as within limit.
    if (is_numeric($limit) && (float) $limit === 0.0
        && (float) ($quota['megabytes_used'] ?? 0) > 0
        && (string) ($quota['under_megabyte_limit'] ?? '') === '1'
        && (string) ($quota['under_quota_overall'] ?? '') === '1') {
        $unlimited = true;
    }
    if (!$unlimited && (!is_numeric($remaining) || (float) $remaining < max(1073741824, $bytes * 4))) {
        echo "STOP: account quota is insufficient or cannot be verified; no snapshot created\n";
        echo 'QUOTA_FIELD_NAMES '.implode(',', array_keys($quota))."\n";
        echo 'QUOTA_SPACE '.json_encode(array_intersect_key($quota, array_flip([
            'megabyte_limit', 'megabytes_used', 'megabytes_remain',
            'under_megabyte_limit', 'under_quota_overall',
        ])), JSON_THROW_ON_ERROR)."\n";
        exit(2);
    }
    $home = realpath(getenv('HOME') ?: '');
    if (!$home || $home === '/' || !str_starts_with($root, $home.DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Unexpected hosting home');
    }
    $privateDirectory = static function (string $path) use ($root, $webRoots): string {
        if (is_link($path)) throw new RuntimeException('Symlink backup directory refused');
        foreach ($webRoots as $webRoot) {
            if ($path === $webRoot || str_starts_with($path, $webRoot.DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('Backup destination is publicly served');
            }
        }
        if ($path === $root || str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Backup destination overlaps source');
        }
        if (!is_dir($path) && !mkdir($path, 0700)) {
            throw new RuntimeException('Cannot create private backup directory');
        }
        if (realpath($path) !== $path || !chmod($path, 0700)) {
            throw new RuntimeException('Unsafe backup directory permissions');
        }
        return $path;
    };
    $backupPhase = 'private-destination-check';
    umask(0077);
    $backupParent = $privateDirectory($home.'/jwtc-private-backups');
    $keyParent = $privateDirectory($home.'/jwtc-backup-keys');
    $id = gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
    $destination = $backupParent.'/'.$id;
    if (!mkdir($destination, 0700)) throw new RuntimeException('Backup destination exists');
    $snapshotTree = static function () use ($root): array {
        $hashes = [];
        $stack = [$root];
        while ($stack) {
            $directory = array_pop($stack);
            foreach (new DirectoryIterator($directory) as $entry) {
                if ($entry->isDot()) continue;
                $path = $entry->getPathname();
                $relative = substr($path, strlen($root) + 1);
                if (in_array($relative, ['.git', 'vendor', 'node_modules',
                    'storage/framework', 'storage/logs', 'bootstrap/cache'], true)) continue;
                if ($entry->isLink()) throw new RuntimeException('Source symlink changed during backup');
                if ($entry->isDir()) {
                    $stack[] = $path;
                } elseif ($entry->isFile() && $entry->isReadable()) {
                    $digest = hash_file('sha256', $path);
                    if (!$digest) throw new RuntimeException('Cannot hash source file');
                    $hashes[$relative] = $digest;
                } else {
                    throw new RuntimeException('Unreadable backup source');
                }
            }
        }
        ksort($hashes, SORT_STRING);
        return $hashes;
    };
    if ($free === false || $free < max(1073741824, $bytes * 4)) {
        throw new RuntimeException('Insufficient free space for verified backups');
    }
    echo "PASS: domain root and private backup destinations verified\n";
    echo "INFO: hashing source files before snapshot; no private names printed\n";
    $backupPhase = 'source-hashing';
    $before = $snapshotTree();
    $dump = $destination.'/database.sql';
    $dumpArgs = [$binary('mysqldump'), '--single-transaction', '--quick', '--skip-lock-tables',
        '--routines', '--events', '--triggers', '--hex-blob', '--no-tablespaces',
        '--default-character-set=utf8mb4', '--skip-add-drop-table', '--result-file='.$dump,
        '--user='.$liveUsername];
    if ($socket !== '') $dumpArgs[] = '--socket='.$socket;
    else { $dumpArgs[] = '--host='.$host; $dumpArgs[] = '--port='.$port; }
    $dumpArgs[] = $liveDatabase;
    $backupPhase = 'sql-export';
    $run($dumpArgs, ['MYSQL_PWD' => $livePassword], 900);
    if (!is_file($dump) || filesize($dump) === 0) throw new RuntimeException('SQL dump is empty');
    echo "PASS: transactional SQL export completed\n";
    $backupPhase = 'file-archive';
    $archive = $destination.'/application-files.tar.gz';
    $run([$binary('tar'), '--create', '--gzip', '--file='.$archive, '--directory='.$root,
        '--exclude=./.git', '--exclude=./vendor', '--exclude=./node_modules',
        '--exclude=./storage/framework', '--exclude=./storage/logs',
        '--exclude=./bootstrap/cache', '.'], [], 900);
    if (!is_file($archive) || filesize($archive) === 0) throw new RuntimeException('File archive is empty');
    $backupPhase = 'source-stability-check';
    $after = $snapshotTree();
    if ($before !== $after) throw new RuntimeException('Source files changed during snapshot; retry required');
    $run([$binary('tar'), '--list', '--gzip', '--file='.$archive], [], 120);
    // Escrow the active Laravel key separately; never print its value.
    if (isset($config)) $appKey = $config['app']['key'] ?? '';
    else $appKey = $value('APP_KEY');
    $rawKey = str_starts_with($appKey, 'base64:')
        ? base64_decode(substr($appKey, 7), true) : $appKey;
    if (!is_string($rawKey) || strlen($rawKey) !== 32) {
        throw new RuntimeException('Original encryption key cannot be escrowed');
    }
    $backupPhase = 'encryption-and-escrow';
    $encryptionKeyFile = $keyParent.'/'.$id.'.key';
    $appKeyFile = $keyParent.'/'.$id.'.app-key';
    if (file_put_contents($encryptionKeyFile, bin2hex(random_bytes(32)), LOCK_EX) === false
        || file_put_contents($appKeyFile, $appKey, LOCK_EX) === false) {
        throw new RuntimeException('Key escrow failed');
    }
    $outputs = [];
    foreach ([$dump, $archive] as $plain) {
        $encrypted = $plain.'.enc';
        $plainHash = hash_file('sha256', $plain);
        $run([$binary('openssl'), 'enc', '-aes-256-cbc', '-pbkdf2', '-iter', '200000',
            '-salt', '-in', $plain, '-out', $encrypted, '-pass', 'file:'.$encryptionKeyFile], [], 900);
        $verified = $plain.'.verify';
        $run([$binary('openssl'), 'enc', '-d', '-aes-256-cbc', '-pbkdf2', '-iter', '200000',
            '-in', $encrypted, '-out', $verified, '-pass', 'file:'.$encryptionKeyFile], [], 900);
        if (!hash_equals($plainHash, hash_file('sha256', $verified))) {
            throw new RuntimeException('Encrypted backup verification failed');
        }
        if (!unlink($verified) || !unlink($plain)) throw new RuntimeException('Backup plaintext cleanup failed');
        $outputs[] = ['file' => basename($encrypted), 'sha256' => hash_file('sha256', $encrypted),
            'plaintext_sha256' => $plainHash, 'bytes' => filesize($encrypted)];
    }
    $manifest = [
        'backup_id' => $id, 'created_at_utc' => gmdate('c'),
        'database_metadata' => $counts,
        'source_files_sha256' => hash('sha256', json_encode($before, JSON_THROW_ON_ERROR)),
        'source_file_count' => count($before),
        'files_stable_during_snapshot' => true,
        'app_key_sha256' => hash('sha256', $appKey),
        'files' => $outputs, 'off_host_copy_verified' => false, 'restore_verified' => false,
        'exclusions' => ['.git', 'vendor', 'node_modules', 'storage/framework', 'storage/logs', 'bootstrap/cache'],
    ];
    file_put_contents($destination.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n", LOCK_EX);
    echo 'BACKUP_ID '.$id."\n";
    echo "PASS: encrypted database and file backups retained privately; decrypt/hash verification passed\n";
    echo "INFO: keys retained separately; no keys/private backups uploaded to GitHub\n";
    echo "INFO: off-host copy, full database restore and staging acceptance remain unverified\n";

} catch (Throwable $error) {
    // Deliberately suppress PDO messages, DB host/schema, password and paths.
    $safeReasons = [
        'Cannot establish domain document root',
        'Separate public document root needs backup mapping',
        'Cannot establish private backup destination',
        'No verified document roots',
        'Backup command failed; details suppressed',
    ];
    $reason = $error->getMessage();
    if (in_array($reason, $safeReasons, true)
        || preg_match('/^Required backup tool unavailable: (uapi|mysqldump|tar|openssl)$/D', $reason)) {
        fwrite(STDERR, "REASON: ".$reason."\n");
    }
    fwrite(STDERR, "FAIL: private backup stopped at ".$backupPhase."; private details suppressed. Retain partial outputs for review.\n");
    exit(1);
}
