<?php
// A private staging-only upgrade rehearsal. Never connect using live credentials.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
set_error_handler(static function ($severity, $message, $file, $line) { echo 'SAFE_WARNING_LINE '.(int)$line.' TYPE '.(int)$severity."\n"; foreach (['Permission denied','File exists','Disk quota exceeded','No space left on device','No such file or directory','Too many open files'] as $reason) if (str_contains($message, $reason)) echo 'SAFE_WARNING_REASON '.$reason."\n"; throw new RuntimeException('Operation failed'); });
$phase = 'identity';
try {
    $liveRoot = realpath('.');
    require $liveRoot.'/vendor/autoload.php';
    $home = realpath(getenv('HOME'));
    $base = $home.'/jwtc-private-staging/20261022-215145-f92ca979';
    $root = $base.'/application';
    foreach ([$base, $root, $base.'/candidate-input-chatfix'] as $path) {
        if (is_link($path) || realpath($path) !== $path || str_starts_with($path, $liveRoot.'/')) throw new RuntimeException('Unsafe staging path');
    }
    $report = json_decode(file_get_contents($base.'/restore-report.json'), true, 16, JSON_THROW_ON_ERROR);
    if (($report['sql_reexport_verified'] ?? false) !== true) throw new RuntimeException('Unexpected restore state');
    if (is_file($base.'/candidate-chatfix-verification.json')) {
        $done = json_decode(file_get_contents($base.'/candidate-chatfix-verification.json'), true, 32, JSON_THROW_ON_ERROR);
        $retained = json_decode(file_get_contents($base.'/candidate-input-chatfix/release-manifest.json'), true, 64, JSON_THROW_ON_ERROR);
        if (($done['candidate_commit'] ?? '') !== ($retained['commit'] ?? null)) throw new RuntimeException('Retained release mismatch');
        foreach ($retained['files'] as $record) {
            if (!hash_equals($record['sha256'], hash_file('sha256', $root.'/'.$record['path']))) throw new RuntimeException('Retained staging code changed');
        }
        echo "PASS: supported staging runtime already verified; all package hashes match\n";
        exit(0);
    }
    $env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    if (($env['DB_DATABASE'] ?? '') !== 'dropoqux_jwtc_stage' || ($env['DB_USERNAME'] ?? '') !== 'dropoqux_jwtc_stage'
        || ($env['APP_ENV'] ?? '') !== 'staging' || ($env['MAIL_MAILER'] ?? '') !== 'array'
        || !hash_equals(hash('sha256', file_get_contents($home.'/jwtc-backup-keys/20261022-215145-f92ca979.app-key')), hash('sha256', $env['APP_KEY'] ?? ''))) throw new RuntimeException('Staging isolation failed');
    $socket = $env['DB_SOCKET'] ?? '';
    $dsn = $socket !== '' ? 'mysql:unix_socket='.$socket : 'mysql:host='.$env['DB_HOST'].';port='.$env['DB_PORT'];
    $pdo = new PDO($dsn.';dbname=dropoqux_jwtc_stage;charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'dropoqux_jwtc_stage') throw new RuntimeException('Wrong database');
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
    $phase = 'before-snapshot';
    $before = $snapshot($pdo);
    if (count($before) < 46 || (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn() !== 60) throw new RuntimeException('Unexpected baseline schema');
    $run = static function (array $args, ?string $cwd = null, array $environment = [], int $timeout = 300): string {
        $process = new Symfony\Component\Process\Process($args, $cwd, $environment);
        $process->setTimeout($timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            echo 'COMMAND_EXIT '.(int)$process->getExitCode()."\n";
            if ($args[0] === '/usr/bin/python3' && preg_match('/GUARD_ERROR [A-Za-z]+ [A-Za-z0-9]+/', $process->getErrorOutput(), $match)) echo $match[0]."\n";
            throw new RuntimeException('Candidate command failed');
        }
        return $process->getOutput();
    };
    $phase = 'candidate-hashes';
    $input = $base.'/candidate-input-chatfix';
    $manifest = json_decode(file_get_contents($input.'/release-manifest.json'), true, 64, JSON_THROW_ON_ERROR);
    if (!preg_match('/^jwtc-[a-f0-9]{40}\.tar\.gz$/D', $manifest['package'] ?? '')
        || !hash_equals($manifest['sha256'], hash_file('sha256', $input.'/'.$manifest['package']))) throw new RuntimeException('Candidate hash mismatch');
    $guard = base64_decode(trim(stream_get_contents(STDIN)), true);
    if (!$guard) throw new RuntimeException('Extractor unavailable');
    $phase = 'extractor-prepare';
    $guard = str_replace('except Exception:', 'except Exception as error:', $guard);
    $guard = str_replace("print('Restore guard failed; private details suppressed', file=sys.stderr)", "print('GUARD_ERROR ' + type(error).__name__ + ' ' + str(getattr(error, 'errno', None)), file=sys.stderr)", $guard);
    $unpacked = $base.'/candidate-unpacked-chatfix';
    if (is_dir($unpacked) && !is_link($unpacked) && !is_file($unpacked.'/.extraction-complete')) {
        if (!rename($unpacked, $unpacked.'-incomplete-'.gmdate('YmdHis'))) throw new RuntimeException('Cannot retain incomplete extraction');
    }
    clearstatcache(true, $unpacked);
    if (is_link($unpacked)) throw new RuntimeException('Candidate destination exists');
    if (!is_dir($unpacked)) {
        if (!mkdir($unpacked, 0700)) throw new RuntimeException('Candidate destination exists');
        $phase = 'extractor-run';
        $run(['/usr/bin/python3', '-c', $guard, 'extract', $input.'/'.$manifest['package'], $unpacked, '536870912']);
        file_put_contents($unpacked.'/.extraction-complete', 'verified extraction');
    }
    $phase = 'extracted-hashes';
    $expected = [];
    foreach ($manifest['files'] as $record) {
        $path = $record['path'];
        if (in_array('..', explode('/', $path), true) || str_starts_with($path, '/') || str_contains($path, '\\')
            || !preg_match('#^(app/|config/|database/|resources/|routes/|public/|vendor/|artisan$|composer\.json$|composer\.lock$|bootstrap/app\.php$)#D', $path)
            || str_starts_with($path, 'public/images/') || str_starts_with($path, 'public/uploads/')
            || !is_file($unpacked.'/'.$path) || is_link($unpacked.'/'.$path)
            || !hash_equals($record['sha256'], hash_file('sha256', $unpacked.'/'.$path))) {
                echo 'CANDIDATE_CODE_PATH '.json_encode($path)."\n";
                echo 'CANDIDATE_CHECKS '.json_encode(['path_rule' => (bool) preg_match('#^(app/|config/|database/|resources/|routes/|public/|vendor/|artisan$|composer\.json$|composer\.lock$|bootstrap/app\.php$)#D', $path), 'file' => is_file($unpacked.'/'.$path), 'hash' => is_file($unpacked.'/'.$path) ? hash_equals($record['sha256'], hash_file('sha256', $unpacked.'/'.$path)) : false])."\n";
                throw new RuntimeException('Candidate file mismatch');
            }
        $expected[$path] = true;
    }
    $actualFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($unpacked, FilesystemIterator::SKIP_DOTS));
    foreach ($actualFiles as $file) {
        if ($file->getFilename() === '.extraction-complete') continue;
        if ($file->isLink() || !$file->isFile() || !isset($expected[substr($file->getPathname(), strlen($unpacked) + 1)])) throw new RuntimeException('Unmanifested candidate file');
    }
    // Verify all restored files that are not approved candidate code remain
    // byte-identical, including private/public uploads and the isolated .env.
    $assets = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $relative = substr($file->getPathname(), strlen($root) + 1);
        if ($file->isLink()) throw new RuntimeException('Staging symlink refused');
        if ($file->isFile() && !isset($expected[$relative])
            && !preg_match('#^(app|config|database|resources|routes|vendor)/#', $relative)
            && !str_starts_with($relative, 'bootstrap/cache/')
            && !str_starts_with($relative, 'storage/framework/') && !str_starts_with($relative, 'storage/logs/')) {
            $assets[$relative] = hash_file('sha256', $file->getPathname());
        }
    }
    $phase = 'private-code-overlay';
    foreach (['app', 'config', 'database', 'resources', 'routes', 'vendor'] as $directory) {
        $directoryPath = $root.'/'.$directory;
        if (is_dir($directoryPath)) {
            $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($entries as $entry) {
                if ($entry->isLink()) throw new RuntimeException('Code symlink refused');
                if ($entry->isDir()) rmdir($entry->getPathname());
                else unlink($entry->getPathname());
            }
            rmdir($directoryPath);
        }
    }
    foreach ($manifest['files'] as $record) {
        $target = $root.'/'.$record['path'];
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true);
        if (!copy($unpacked.'/'.$record['path'], $target)) throw new RuntimeException('Candidate copy failed');
        chmod($target, 0600);
    }
    $isolated = [
        'APP_ENV' => 'staging', 'APP_DEBUG' => 'false', 'APP_URL' => 'http://127.0.0.1',
        'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'dropoqux_jwtc_stage', 'DB_USERNAME' => 'dropoqux_jwtc_stage',
        'DB_PASSWORD' => $env['DB_PASSWORD'], 'DB_HOST' => $env['DB_HOST'], 'DB_PORT' => $env['DB_PORT'], 'DB_SOCKET' => $socket,
        'APP_KEY' => $env['APP_KEY'], 'MAIL_MAILER' => 'array', 'CACHE_DRIVER' => 'array', 'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'null',
    ];
    foreach (glob($root.'/bootstrap/cache/*.php') as $cache) unlink($cache);
    $phase = 'platform-and-boot';
    $composer = $home.'/bin/composer';
    $run([PHP_BINARY, $composer, 'check-platform-reqs', '--no-dev'], $root, $isolated);
    $run([PHP_BINARY, 'artisan', 'package:discover', '--no-interaction'], $root, $isolated);
    $phase = 'staging-migrations';
    $run([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], $root, $isolated);
    $run([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], $root, $isolated);
    $phase = 'preservation';
    $after = $snapshot($pdo, $before);
    foreach ($before as $table => $state) {
        foreach ($state['hashes'] as $hash => $count) {
            if (($after[$table]['hashes'][$hash] ?? 0) !== $count) throw new RuntimeException('Historical row changed');
        }
        if (!in_array($table, ['migrations', 'permissions'], true) && $after[$table]['hashes'] !== $state['hashes']) throw new RuntimeException('Unexpected historical data change');
    }
    foreach ($assets as $path => $hash) {
        if (!is_file($root.'/'.$path) || !hash_equals($hash, hash_file('sha256', $root.'/'.$path))) throw new RuntimeException('Upload or environment changed');
    }
    $migrations = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
    if ($migrations !== 60) throw new RuntimeException('Unexpected migration count');
    $phase = 'route-and-template-check';
    $run([PHP_BINARY, 'artisan', 'route:list', '--except-vendor', '--json'], $root, $isolated);
    $run([PHP_BINARY, 'artisan', 'view:cache'], $root, $isolated);
    $run([PHP_BINARY, 'artisan', 'view:clear'], $root, $isolated);
    $result = ['verified_at_utc' => gmdate('c'), 'candidate_commit' => $manifest['commit'],
        'artifact_run' => 39900543696, 'migrations' => $migrations, 'historical_tables_verified' => count($before),
        'untouched_files_verified' => count($assets), 'production_modified' => false,
        'browser_acceptance_verified' => false, 'smtp_delivery_verified' => false];
    file_put_contents($base.'/candidate-chatfix-verification.json', json_encode($result, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
    echo "PASS: candidate production dependencies, platform, Laravel boot, routes and Blade templates\n";
    echo "PASS: supported runtime upgrade; migrations idempotent; 60 migration records\n";
    echo "PASS: all original rows in existing tables preserved; original grants, keys, uploads and environment retained\n";
    echo 'PRESERVED_FILE_COUNT '.count($assets)."\n";
    echo "INFO: browser flows and real SMTP delivery remain unverified; live application unchanged\n";
} catch (Throwable $error) {
    echo 'SAFE_ERROR_CLASS '.get_class($error)."\n";
    if (in_array($error->getMessage(), ['Candidate hash mismatch', 'Candidate file mismatch', 'Unmanifested candidate file', 'Candidate destination exists', 'Candidate command failed'], true)) fwrite(STDERR, 'REASON: '.$error->getMessage()."\n");
    fwrite(STDERR, 'FAIL: private staging candidate stopped at '.$phase."; private details suppressed; preserve staging for investigation\n");
    exit(1);
}
