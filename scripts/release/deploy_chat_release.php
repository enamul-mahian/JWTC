<?php
// Explicitly authorized production promotion; additive migrations only.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
set_error_handler(static function () { throw new RuntimeException('Deployment operation failed'); });
$phase = 'preflight';
$maintenance = false;
$moved = [];
$changed = [];
$rollback = null;
$root = realpath('.');
$command = static function (array $args, string $cwd, array $env = [], int $timeout = 180): string {
    $environment = getenv();
    foreach ($env as $key => $value) $environment[$key] = $value;
    $pipes = [];
    $p = proc_open($args, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd, $environment);
    if (!is_resource($p)) throw new RuntimeException('Cannot start command');
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $start = time(); $output = ''; $exit = null;
    do {
        $output .= stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]); // Private errors are never printed.
        $status = proc_get_status($p);
        if (!$status['running']) { $exit = $status['exitcode']; break; }
        if (time() - $start > $timeout) { proc_terminate($p); throw new RuntimeException('Command timeout'); }
        usleep(50000);
    } while (true);
    $output .= stream_get_contents($pipes[1]);
    fclose($pipes[1]); fclose($pipes[2]);
    $closed = proc_close($p);
    if (($exit === -1 ? $closed : $exit) !== 0) throw new RuntimeException('Deployment command failed');
    return $output;
};
try {
    require $root.'/vendor/autoload.php';
    $home = realpath(getenv('HOME'));
    if ($root !== $home.'/joingallagher.uk' || is_link($root)) throw new RuntimeException('Wrong live root');
    $base = $home.'/jwtc-private-staging/20261022-215145-f92ca979';
    $gate = json_decode(file_get_contents($base.'/chatfix-predeploy-gate.json'), true, 32, JSON_THROW_ON_ERROR);
    $stageReport = json_decode(file_get_contents($base.'/candidate-chatfix-verification.json'), true, 32, JSON_THROW_ON_ERROR);
    $release = $base.'/candidate-unpacked-chatfix';
    $manifest = json_decode(file_get_contents($base.'/candidate-input-chatfix/release-manifest.json'), true, 64, JSON_THROW_ON_ERROR);
    if (($gate['http_smoke'] ?? false) !== true || ($gate['audit'] ?? null) !== []
        || ($stageReport['migrations'] ?? 0) !== 60 || $gate['candidate_commit'] !== $manifest['commit']
        || $stageReport['candidate_commit'] !== $manifest['commit']
        || time() - strtotime($gate['passed_at_utc']) > 1800) throw new RuntimeException('Release gate mismatch');
    foreach ([$base, $release] as $path) if (realpath($path) !== $path || is_link($path)) throw new RuntimeException('Unsafe private release');
    $envText = file_get_contents($root.'/.env');
    $env = Dotenv\Dotenv::parse($envText);
    $cached = is_file($root.'/bootstrap/cache/config.php') ? require $root.'/bootstrap/cache/config.php' : null;
    if ($cached) {
        $db = $cached['database']['connections'][$cached['database']['default']] ?? [];
        if (($cached['database']['default'] ?? '') !== 'mysql' || !empty($db['url'])) throw new RuntimeException('Unsupported live configuration');
        foreach (['database' => 'DB_DATABASE', 'username' => 'DB_USERNAME', 'password' => 'DB_PASSWORD'] as $name => $key) {
            if ((string) ($db[$name] ?? '') !== (string) ($env[$key] ?? '')) throw new RuntimeException('Cached environment differs');
        }
        if (($cached['app']['key'] ?? '') !== ($env['APP_KEY'] ?? '')) throw new RuntimeException('Cached app key differs');
    }
    if (($env['DB_CONNECTION'] ?? 'mysql') !== 'mysql' || ($env['DB_DATABASE'] ?? '') === 'dropoqux_jwtc_stage'
        || empty($env['DB_DATABASE']) || !empty($env['DB_URL']) || !empty($env['DATABASE_URL'])) throw new RuntimeException('Wrong live database');
    $keyHash = hash('sha256', $env['APP_KEY'] ?? '');
    if (!hash_equals(hash('sha256', file_get_contents($home.'/jwtc-backup-keys/20261022-215145-f92ca979.app-key')), $keyHash)) throw new RuntimeException('Original key changed');
    $socket = $env['DB_SOCKET'] ?? '';
    $dsn = $socket !== '' ? 'mysql:unix_socket='.$socket : 'mysql:host='.($env['DB_HOST'] ?? '127.0.0.1').';port='.($env['DB_PORT'] ?? '3306');
    $pdo = new PDO($dsn.';dbname='.$env['DB_DATABASE'].';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== $env['DB_DATABASE']
        || (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn() !== 60) throw new RuntimeException('Unexpected source schema');
    $backupManifests = glob($home.'/jwtc-private-backups/*/manifest.json');
    rsort($backupManifests, SORT_STRING);
    $fresh = json_decode(file_get_contents($backupManifests[0] ?? ''), true, 32, JSON_THROW_ON_ERROR);
    if (time() - strtotime($fresh['created_at_utc']) > 900 || ($fresh['files_stable_during_snapshot'] ?? false) !== true
        || !hash_equals($fresh['app_key_sha256'], $keyHash)) throw new RuntimeException('Fresh verified backup required');
    foreach ($fresh['files'] as $record) {
        if (!hash_equals($record['sha256'], hash_file('sha256', dirname($backupManifests[0]).'/'.$record['file']))) throw new RuntimeException('Fresh backup mismatch');
    }
    $phase = 'candidate-verification';
    $records = [];
    foreach ($manifest['files'] as $record) {
        $path = $record['path'];
        if (in_array('..', explode('/', $path), true) || str_starts_with($path, '/') || str_contains($path, '\\')
            || !preg_match('#^(app/|config/|database/|resources/|routes/|public/|vendor/|artisan$|composer\.json$|composer\.lock$|bootstrap/app\.php$)#D', $path)
            || is_link($release.'/'.$path) || !is_file($release.'/'.$path)
            || !hash_equals($record['sha256'], hash_file('sha256', $release.'/'.$path))) throw new RuntimeException('Candidate bytes changed');
        $records[$path] = $record['sha256'];
    }
    $id = gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
    $parent = $home.'/jwtc-private-deployments';
    $uapi = null;
    foreach (['/usr/bin/uapi', '/bin/uapi', '/usr/local/bin/uapi', '/usr/local/cpanel/bin/uapi'] as $path) if (is_executable($path)) { $uapi = $path; break; }
    if (!$uapi) throw new RuntimeException('Domain mapping unavailable');
    $domains = json_decode($command([$uapi, '--output=json', 'DomainInfo', 'domains_data', 'format=list'], $root), true, 64, JSON_THROW_ON_ERROR);
    if (($domains['result']['status'] ?? 0) !== 1) throw new RuntimeException('Domain mapping failed');
    $verifyPrivate = static function ($data) use (&$verifyPrivate, $parent): void {
        foreach ((array) $data as $key => $value) {
            if (in_array($key, ['documentroot', 'document_root'], true) && is_string($value)) {
                $web = realpath($value);
                if (!$web || $parent === $web || str_starts_with($parent, $web.'/')) throw new RuntimeException('Rollback destination is public');
            } elseif (is_array($value)) $verifyPrivate($value);
        }
    };
    $verifyPrivate($domains['result']['data'] ?? []);
    if (is_link($parent) || (file_exists($parent) && realpath($parent) !== $parent)) throw new RuntimeException('Unsafe rollback parent');
    if (!is_dir($parent)) mkdir($parent, 0700);
    chmod($parent, 0700);
    $rollback = $parent.'/'.$id;
    mkdir($rollback, 0700);
    mkdir($rollback.'/previous', 0700);
    mkdir($rollback.'/prepared', 0700);
    copy($root.'/.env', $rollback.'/previous/.env');
    chmod($rollback.'/previous/.env', 0600);
    $groups = ['app', 'config', 'database', 'resources', 'routes', 'vendor'];
    foreach ($groups as $group) {
        $command(['/bin/cp', '-a', $release.'/'.$group, $rollback.'/prepared/'.$group], $root);
        $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rollback.'/prepared/'.$group, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        chmod($rollback.'/prepared/'.$group, 0755);
        foreach ($entries as $entry) {
            if ($entry->isLink()) throw new RuntimeException('Prepared symlink');
            chmod($entry->getPathname(), $entry->isDir() ? 0755 : 0644);
        }
    }
    mkdir($rollback.'/previous/bootstrap/cache', 0700, true);
    foreach (glob($root.'/bootstrap/cache/*.php') as $cache) copy($cache, $rollback.'/previous/bootstrap/cache/'.basename($cache));
    $phase = 'maintenance';
    $command([PHP_BINARY, 'artisan', 'down', '--retry=60'], $root);
    $maintenance = true;
    usleep(3000000);
    $snapshot = static function (PDO $pdo, ?array $previous = null): array {
        $result = [];
        $tables = $previous === null ? $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) : array_keys($previous);
        foreach ($tables as $table) {
            if (!preg_match('/^[A-Za-z0-9_]+$/D', $table)) throw new RuntimeException('Unexpected table');
            $columns = $previous[$table]['columns'] ?? $pdo->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_COLUMN);
            $names = implode(',', array_map(static fn ($name) => '`'.str_replace('`', '``', $name).'`', $columns));
            $hashes = [];
            $statement = $pdo->query('SELECT '.$names.' FROM `'.$table.'`');
            while ($row = $statement->fetch(PDO::FETCH_NUM)) {
                $hash = hash('sha256', serialize($row));
                $hashes[$hash] = ($hashes[$hash] ?? 0) + 1;
            }
            ksort($hashes);
            $result[$table] = ['columns' => $columns, 'hashes' => $hashes];
        }
        return $result;
    };
    $phase = 'data-snapshot';
    $before = $snapshot($pdo);
    if (count($before) < 46) throw new RuntimeException('Unexpected source table count');
    $protected = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    // Source had no symlinks except dependency-bin links; code groups are
    // replaced from verified CI bytes. Hash all remaining retained files.
    $collectProtected = static function (string $directory, string $relative = '') use (&$collectProtected, &$protected, $groups, $records): void {
        foreach (new DirectoryIterator($directory) as $entry) {
            if ($entry->isDot()) continue;
            $name = ltrim($relative.'/'.$entry->getFilename(), '/');
            if ($relative === '' && in_array($entry->getFilename(), array_merge($groups, ['.git', 'node_modules']), true)) continue;
            if (in_array($name, ['bootstrap/cache', 'storage/framework', 'storage/logs'], true)
                || isset($records[$name]) || $name === '.env') continue;
            if ($entry->isLink()) throw new RuntimeException('Retained file symlink');
            if ($entry->isDir()) $collectProtected($entry->getPathname(), $name);
            elseif ($entry->isFile()) $protected[$name] = hash_file('sha256', $entry->getPathname());
        }
    };
    $collectProtected($root);
    $phase = 'promote-code';
    foreach ($groups as $group) {
        if (!rename($root.'/'.$group, $rollback.'/previous/'.$group)) throw new RuntimeException('Cannot retain previous code');
        $moved[] = $group;
        if (!rename($rollback.'/prepared/'.$group, $root.'/'.$group)) throw new RuntimeException('Cannot promote prepared code');
    }
    foreach ($records as $path => $hash) {
        if (in_array(explode('/', $path)[0], $groups, true)) continue;
        $target = $root.'/'.$path;
        if (is_link($target)) throw new RuntimeException('Public target symlink');
        $old = $rollback.'/previous/'.$path;
        if (!is_dir(dirname($old))) mkdir(dirname($old), 0700, true);
        $exists = is_file($target);
        if ($exists && !copy($target, $old)) throw new RuntimeException('Cannot retain public code');
        $changed[$path] = $exists;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
        $temp = $target.'.jwtc-'.$id;
        if (!copy($release.'/'.$path, $temp)) throw new RuntimeException('Cannot prepare public code');
        chmod($temp, 0644);
        if (!rename($temp, $target)) throw new RuntimeException('Cannot promote public code');
    }
    // Preserve all production credentials and keys; correct only debug/env
    // switches where required, retaining the exact previous environment privately.
    foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false'] as $name => $value) {
        if (preg_match('/^'.preg_quote($name, '/').'\s*=/m', $envText)) $envText = preg_replace('/^'.preg_quote($name, '/').'\s*=.*$/m', $name.'='.$value, $envText);
        else $envText .= "\n".$name.'='.$value."\n";
    }
    $tempEnv = $root.'/.env.jwtc-'.$id;
    file_put_contents($tempEnv, $envText, LOCK_EX); chmod($tempEnv, 0600); rename($tempEnv, $root.'/.env');
    $phase = 'runtime-caches';
    // Preserve previous cache files for code rollback, never clear sessions or
    // application cache stores. Only framework-generated config/routes/views.
    foreach (glob($root.'/bootstrap/cache/*.php') as $cache) {
        unlink($cache);
    }
    $command([PHP_BINARY, 'artisan', 'package:discover', '--no-interaction'], $root);
    $phase = 'production-additive-migrations';
    $command([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], $root);
    $command([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], $root);
    $phase = 'preservation-verification';
    $after = $snapshot($pdo, $before);
    foreach ($before as $table => $state) {
        foreach ($state['hashes'] as $hash => $count) {
            if (($after[$table]['hashes'][$hash] ?? 0) !== $count) throw new RuntimeException('Historical data changed');
        }
        if (!in_array($table, ['migrations', 'permissions'], true) && $state['hashes'] !== $after[$table]['hashes']) throw new RuntimeException('Unexpected legacy row addition');
    }
    foreach ($protected as $path => $hash) {
        if (!is_file($root.'/'.$path) || !hash_equals($hash, hash_file('sha256', $root.'/'.$path))) throw new RuntimeException('Retained upload changed');
    }
    $newEnv = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    foreach ($env as $name => $value) {
        if (!in_array($name, ['APP_ENV', 'APP_DEBUG'], true) && ($newEnv[$name] ?? null) !== $value) throw new RuntimeException('Environment value changed');
    }
    if ((int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn() !== 60) throw new RuntimeException('Migration count differs');
    $command([PHP_BINARY, 'artisan', 'config:cache'], $root);
    $command([PHP_BINARY, 'artisan', 'view:cache'], $root);
    $command([PHP_BINARY, 'artisan', 'route:list', '--except-vendor', '--json'], $root);
    $phase = 'return-live';
    $command([PHP_BINARY, 'artisan', 'up'], $root);
    $maintenance = false;
    usleep(2000000);
    $phase = 'public-http';
    foreach (['/' => 200, '/login' => 200, '/register' => 200, '/contact' => 200, '/company/login' => 200, '/company/register' => 200, '/jobseeker/login' => 200, '/search-jobs' => 200, '/site-chat/state' => 200, '/admin/site-chat' => 302, '/admin/dashboard' => 302] as $path => $expected) {
        $status = trim($command(['/usr/bin/curl', '--silent', '--show-error', '--max-time', '20', '--output', '/dev/null', '--write-out', '%{http_code}', '--header', 'Cache-Control: no-cache', 'https://joingallagher.uk'.$path], $root));
        echo 'LIVE_HTTP '.$path.' '.$status."\n";
        if ((int) $status !== $expected) throw new RuntimeException('Public HTTP failed');
    }
    $homepage = $command(['/usr/bin/curl', '--silent', '--show-error', '--fail', '--max-time', '20', '--header', 'Cache-Control: no-cache', 'https://joingallagher.uk/'], $root);
    if (!str_contains($homepage, 'Find Your Next Opportunity') || !str_contains($homepage, 'theme/approved.css') || !str_contains($homepage, 'site-chat/widget.js') || str_contains($homepage, 'tawk.to')) throw new RuntimeException('Approved homepage missing');
    file_put_contents($rollback.'/deployment-report.json', json_encode([
        'id' => $id, 'deployed_at_utc' => gmdate('c'), 'candidate_commit' => $manifest['commit'],
        'fresh_backup_id' => $fresh['backup_id'], 'migrations' => 60,
        'historical_tables_verified' => count($before), 'retained_files_verified' => count($protected),
        'previous_code_retained' => true, 'http_smoke_passed' => true,
        'feature_acceptance_complete' => false,
    ], JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    echo 'DEPLOYMENT_ID '.$id."\n";
    echo 'FRESH_BACKUP_ID '.$fresh['backup_id']."\n";
    echo "PASS: live release promoted with all legacy data, uploads and original key preserved\n";
    echo "PASS: 60 migration records; public HTTP checks passed; previous code retained privately\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: production promotion at '.$phase."; private details suppressed\n");
    if ($rollback && ($moved || $changed)) {
        try {
            if (!$maintenance) { $command([PHP_BINARY, 'artisan', 'down', '--retry=60'], $root); $maintenance = true; }
            foreach (array_reverse($moved) as $group) {
                if (is_dir($root.'/'.$group)) rename($root.'/'.$group, $rollback.'/failed-'.$group);
                if (!rename($rollback.'/previous/'.$group, $root.'/'.$group)) throw new RuntimeException('Rollback rename failed');
            }
            foreach ($changed as $path => $existed) {
                if ($existed) copy($rollback.'/previous/'.$path, $root.'/'.$path);
                elseif (is_file($root.'/'.$path)) unlink($root.'/'.$path);
            }
            copy($rollback.'/previous/.env', $root.'/.env'); chmod($root.'/.env', 0600);
            foreach (glob($root.'/bootstrap/cache/*.php') as $path) unlink($path);
            foreach (glob($rollback.'/previous/bootstrap/cache/*.php') as $path) copy($path, $root.'/bootstrap/cache/'.basename($path));
            $command([PHP_BINARY, 'artisan', 'view:clear'], $root);
            $command([PHP_BINARY, 'artisan', 'up'], $root);
            $maintenance = false;
            echo "ROLLBACK: previous application code restored; additive database changes retained without deleting data\n";
        } catch (Throwable $rollbackError) {
            fwrite(STDERR, "ROLLBACK FAILED: application kept under maintenance; private previous code retained for recovery\n");
        }
    } elseif ($maintenance) {
        try { $command([PHP_BINARY, 'artisan', 'up'], $root); $maintenance = false; } catch (Throwable $ignore) {}
    }
    exit(1);
}
