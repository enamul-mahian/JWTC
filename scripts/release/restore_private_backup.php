<?php

// Restore one verified backup into an empty independent staging DB and private files.
// Never bootstrap Laravel or modify live files/database.
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
    if (!$unlimited && (!is_numeric($remaining) || (float) $remaining < max(2147483648, $bytes * 6))) {
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
    $backupPhase = 'staging-permission-check';
    // Reject inherited roles, global privileges, wildcard schemas and access to
    // any other schema. This is checked before parsing/importing backup SQL.
    foreach ($pdo->query('SHOW GRANTS')->fetchAll(PDO::FETCH_COLUMN) as $grant) {
        if (preg_match('/^GRANT USAGE ON \*\.\* TO /D', $grant)
            && !str_contains($grant, 'WITH GRANT OPTION')) continue;
        if (!preg_match('/^GRANT ([A-Z ,]+) ON `([^`]+)`\.\* TO /D', $grant, $match)
            || $match[2] !== str_replace('_', '\\_', $input['database'])
            || str_contains($match[2], '%') || str_contains($grant, 'WITH GRANT OPTION')) {
            throw new RuntimeException('Staging grants are not schema restricted');
        }
    }
    echo "PASS: staging grants restricted to the independent staging schema\n";
    $backupPhase = 'backup-manifest';
    $id = '20261022-215145-f92ca979';
    $backup = $home.'/jwtc-private-backups/'.$id;
    $keys = $home.'/jwtc-backup-keys';
    foreach ([$backup, $keys] as $path) {
        if (is_link($path) || realpath($path) !== $path) throw new RuntimeException('Unsafe retained backup path');
        foreach ($webRoots as $webRoot) {
            if ($path === $webRoot || str_starts_with($path, $webRoot.'/')) {
                throw new RuntimeException('Retained backup is publicly served');
            }
        }
    }
    $manifest = json_decode(file_get_contents($backup.'/manifest.json'), true, 64, JSON_THROW_ON_ERROR);
    if (($manifest['backup_id'] ?? '') !== $id || ($manifest['files_stable_during_snapshot'] ?? false) !== true
        || count($manifest['files'] ?? []) !== 2) throw new RuntimeException('Unexpected backup manifest');
    foreach (['non_innodb_tables', 'views', 'triggers_visible', 'routines_visible', 'events_visible'] as $kind) {
        if (($manifest['database_metadata'][$kind] ?? -1) !== 0) throw new RuntimeException('Stored objects require separate restore review');
    }
    $appKey = file_get_contents($keys.'/'.$id.'.app-key');
    if (!hash_equals($manifest['app_key_sha256'], hash('sha256', $appKey))) throw new RuntimeException('Original app key mismatch');
    $helper = base64_decode($input['helper_base64'] ?? '', true);
    if (!$helper || strlen($helper) > 30000) throw new RuntimeException('Restore guard unavailable');
    $python = $binary('python3');
    $mysql = $binary('mysql');
    $openssl = $binary('openssl');
    if ($free === false || $free < max(2147483648, $bytes * 6)) throw new RuntimeException('Insufficient restore space');
    umask(0077);
    $parent = $privateDirectory($home.'/jwtc-private-staging');
    $destination = $parent.'/'.$id;
    if (file_exists($destination) || is_link($destination)) throw new RuntimeException('Staging restore destination exists');
    if (!mkdir($destination, 0700)) throw new RuntimeException('Cannot create staging restore');
    $application = $destination.'/application';
    if (!mkdir($application, 0700)) throw new RuntimeException('Cannot create staging files');
    $backupPhase = 'decrypt-verify';
    $plainFiles = [];
    foreach ($manifest['files'] as $record) {
        $name = $record['file'] ?? '';
        if (!in_array($name, ['database.sql.enc', 'application-files.tar.gz.enc'], true)
            || isset($plainFiles[$name]) || is_link($backup.'/'.$name)
            || !hash_equals($record['sha256'], hash_file('sha256', $backup.'/'.$name))) {
            throw new RuntimeException('Encrypted archive mismatch');
        }
        $plain = $destination.'/'.substr($name, 0, -4);
        $run([$openssl, 'enc', '-d', '-aes-256-cbc', '-pbkdf2', '-iter', '200000',
            '-in', $backup.'/'.$name, '-out', $plain, '-pass', 'file:'.$keys.'/'.$id.'.key'], [], 900);
        if (!hash_equals($record['plaintext_sha256'], hash_file('sha256', $plain))) throw new RuntimeException('Plaintext archive mismatch');
        $plainFiles[$name] = $plain;
    }
    echo "PASS: encrypted and decrypted archive hashes match retained backup manifest\n";
    $backupPhase = 'archive-extraction';
    $run([$python, '-c', $helper, 'extract', $plainFiles['application-files.tar.gz.enc'],
        $application, (string) ($bytes * 2)], [], 900);
    $hashes = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($application, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isLink() || !$file->isFile()) throw new RuntimeException('Unexpected extracted entry');
        $hashes[substr($file->getPathname(), strlen($application) + 1)] = hash_file('sha256', $file->getPathname());
    }
    ksort($hashes, SORT_STRING);
    if (count($hashes) !== $manifest['source_file_count']
        || !hash_equals($manifest['source_files_sha256'], hash('sha256', json_encode($hashes, JSON_THROW_ON_ERROR)))) {
        throw new RuntimeException('Restored file inventory mismatch');
    }
    echo 'PASS: restored file count and every file hash match original snapshot ('.count($hashes)." files)\n";
    $backupPhase = 'staging-environment';
    // Replace the copied production environment before any possible framework
    // invocation. No application/queue/scheduler/web process is started here.
    $envQuote = static fn (string $value): string => '"'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '', ''], $value).'"';
    $stagingEnv = [
        'APP_NAME' => 'JWTC Private Staging', 'APP_ENV' => 'staging', 'APP_DEBUG' => 'false',
        'APP_URL' => 'http://127.0.0.1', 'APP_KEY' => $appKey,
        'DB_CONNECTION' => 'mysql', 'DB_HOST' => $host, 'DB_PORT' => (string) $port,
        'DB_SOCKET' => $socket, 'DB_DATABASE' => $input['database'], 'DB_USERNAME' => $input['username'],
        'DB_PASSWORD' => $input['password'], 'MAIL_MAILER' => 'array', 'MAIL_DRIVER' => 'array',
        'QUEUE_CONNECTION' => 'null', 'CACHE_STORE' => 'array', 'CACHE_DRIVER' => 'array',
        'SESSION_DRIVER' => 'array', 'BROADCAST_CONNECTION' => 'null', 'BROADCAST_DRIVER' => 'null',
        'LOG_CHANNEL' => 'single',
    ];
    $text = '';
    foreach ($stagingEnv as $name => $value) $text .= $name.'='.$envQuote($value)."\n";
    if (file_put_contents($application.'/.env', $text, LOCK_EX) === false) throw new RuntimeException('Cannot isolate staging environment');
    foreach (['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views',
        'storage/logs', 'bootstrap/cache'] as $path) {
        if (!is_dir($application.'/'.$path) && !mkdir($application.'/'.$path, 0700, true)) throw new RuntimeException('Cannot create private runtime directories');
    }
    $backupPhase = 'sql-safety-validation';
    $sql = $plainFiles['database.sql.enc'];
    $run([$python, '-c', $helper, 'sql', $sql], [], 300);
    // Recheck immediately before the first DB write; never overwrite a partial
    // or previously populated staging database on retries.
    $empty = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
    $empty->execute([$input['database']]);
    if ((int) $empty->fetchColumn() !== 0) throw new RuntimeException('Staging no longer empty');
    $pdo->exec('SET SESSION TRANSACTION READ WRITE');
    $args = [$mysql, '--binary-mode', '--default-character-set=utf8mb4', '--user='.$input['username']];
    if ($socket !== '') $args[] = '--socket='.$socket;
    else { $args[] = '--host='.$host; $args[] = '--port='.$port; }
    foreach ([PDO::MYSQL_ATTR_SSL_CA => '--ssl-ca=', PDO::MYSQL_ATTR_SSL_CERT => '--ssl-cert=',
        PDO::MYSQL_ATTR_SSL_KEY => '--ssl-key='] as $option => $flag) {
        if (!empty($ssl[$option])) $args[] = $flag.$ssl[$option];
    }
    $args[] = $input['database'];
    $backupPhase = 'staging-sql-import';
    $stream = fopen($sql, 'rb');
    $process = new Symfony\Component\Process\Process($args, null, ['MYSQL_PWD' => $input['password']]);
    $process->setInput($stream);
    $process->setTimeout(900);
    $process->run();
    fclose($stream);
    if (!$process->isSuccessful()) throw new RuntimeException('Staging import failed; preserve partial restore');
    $backupPhase = 'restore-verification';
    $empty->execute([$input['database']]);
    $tableCount = (int) $empty->fetchColumn();
    if ($tableCount !== $manifest['database_metadata']['base_tables']) throw new RuntimeException('Restored table count mismatch');
    // Re-export with the same server binary/options and compare SQL including
    // all CREATE/INSERT/session statements. Only human metadata comments differ.
    $redump = $destination.'/restored-verification.sql';
    $dumpArgs = [$binary('mysqldump'), '--single-transaction', '--quick', '--skip-lock-tables',
        '--routines', '--events', '--triggers', '--hex-blob', '--no-tablespaces',
        '--default-character-set=utf8mb4', '--skip-add-drop-table', '--result-file='.$redump,
        '--user='.$input['username']];
    if ($socket !== '') $dumpArgs[] = '--socket='.$socket;
    else { $dumpArgs[] = '--host='.$host; $dumpArgs[] = '--port='.$port; }
    foreach ([PDO::MYSQL_ATTR_SSL_CA => '--ssl-ca=', PDO::MYSQL_ATTR_SSL_CERT => '--ssl-cert=',
        PDO::MYSQL_ATTR_SSL_KEY => '--ssl-key='] as $option => $flag) {
        if (!empty($ssl[$option])) $dumpArgs[] = $flag.$ssl[$option];
    }
    $dumpArgs[] = $input['database'];
    $run($dumpArgs, ['MYSQL_PWD' => $input['password']], 900);
    $canonicalHash = static function (string $path): string {
        $context = hash_init('sha256');
        $handle = fopen($path, 'rb');
        while (($line = fgets($handle)) !== false) {
            if (str_starts_with($line, '--') || trim($line) === '') continue;
            hash_update($context, $line);
        }
        fclose($handle);
        return hash_final($context);
    };
    if (!hash_equals($canonicalHash($sql), $canonicalHash($redump))) throw new RuntimeException('Re-export differs from original SQL; investigate staging only');
    foreach ([$sql, $redump, $plainFiles['application-files.tar.gz.enc']] as $path) {
        if (!unlink($path)) throw new RuntimeException('Private temporary cleanup failed');
    }
    file_put_contents($destination.'/restore-report.json', json_encode([
        'backup_id' => $id, 'restored_at_utc' => gmdate('c'),
        'tables' => $tableCount, 'files' => count($hashes),
        'file_hashes_verified' => true, 'sql_reexport_verified' => true,
        'original_app_key_verified' => true, 'off_host_copy' => 'user-reported downloaded',
        'application_booted' => false, 'production_modified' => false,
    ], JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n", LOCK_EX);
    echo 'PASS: '.$tableCount." staging tables restored; complete SQL re-export matches backup\n";
    echo "PASS: original APP_KEY preserved; staging environment isolated; private temporary archives removed\n";
    echo 'STAGING_RESTORE_ID '.$id."\n";
    echo "INFO: no framework, migrations, email, queue or web process started; live unchanged\n";
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: staging restore stopped at ".$backupPhase."; private details suppressed. Preserve partial staging outputs; do not retry over existing data.\n");
    exit(1);
}
