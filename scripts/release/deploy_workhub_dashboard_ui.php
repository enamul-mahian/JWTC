<?php

// Small, backup-gated production promotion for the Work Hub admin URL import.
// Changes four PHP/Blade files only. No SQL, migrations, deletes or config rewrites.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
$phase = 'validation';
$changed = [];
$rollback = null;
$root = realpath('.');
$run = static function (array $args, string $cwd, int $timeout = 90): string {
    $process = new Symfony\Component\Process\Process($args, $cwd);
    $process->setTimeout($timeout);
    $process->run();
    if (!$process->isSuccessful()) throw new RuntimeException('Required release check failed');
    return $process->getOutput();
};

try {
    if (!$root || !is_file($root.'/vendor/autoload.php')) throw new RuntimeException('Missing Laravel runtime');
    require_once $root.'/vendor/autoload.php';
    $home = realpath(getenv('HOME') ?: '');
    if (!$home || $root !== $home.'/joingallagher.uk' || is_link($root)) throw new RuntimeException('Unexpected production location');
    $payload = json_decode(stream_get_contents(STDIN), true, 12, JSON_THROW_ON_ERROR);
    $sha = $payload['release_sha'] ?? '';
    if (!is_string($sha) || !preg_match('/^[a-f0-9]{40}$/D', $sha)) throw new RuntimeException('Invalid release identity');

    $allowed = [
        'app/Http/Controllers/Admin/ExternalJobImportController.php',
        'app/Services/WorkHubLinkImporter.php',
        'resources/views/admin/job/external-import.blade.php',
        'routes/web.php',
    ];
    $hashes = $payload['hashes'] ?? null;
    if (!is_array($hashes) || count($hashes) !== count($allowed)) throw new RuntimeException('Release file list mismatch');
    foreach ($allowed as $relative) {
        if (!isset($hashes[$relative]) || !is_string($hashes[$relative])
            || !preg_match('/^[a-f0-9]{64}$/D', $hashes[$relative])) throw new RuntimeException('Release file hash missing');
    }

    $package = $home.'/jwtc-private-workhub-incoming/'.$sha.'/release.tar.gz';
    if (!is_file($package) || is_link($package)
        || !hash_equals($payload['archive_sha256'] ?? '', hash_file('sha256', $package))) {
        throw new RuntimeException('Private archive hash mismatch');
    }
    $listed = explode("\n", trim($run(['/bin/tar', '-tzf', $package], $root)));
    sort($listed);
    $want = $allowed;
    sort($want);
    if ($listed !== $want) throw new RuntimeException('Unexpected archive entries');

    // The immediately preceding workflow step must have retained a fresh encrypted backup.
    $manifests = glob($home.'/jwtc-private-backups/*/manifest.json');
    rsort($manifests, SORT_STRING);
    $manifestPath = $manifests[0] ?? null;
    if (!$manifestPath || !is_file($manifestPath)) throw new RuntimeException('Recent backup unavailable');
    $backup = json_decode(file_get_contents($manifestPath), true, 64, JSON_THROW_ON_ERROR);
    $age = time() - strtotime($backup['created_at_utc'] ?? '1970-01-01');
    if ($age < -120 || $age > 900 || ($backup['files_stable_during_snapshot'] ?? false) !== true
        || count($backup['files'] ?? []) !== 2) throw new RuntimeException('Fresh backup verification failed');
    $env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    $key = (string) ($env['APP_KEY'] ?? '');
    if (!hash_equals($backup['app_key_sha256'] ?? '', hash('sha256', $key))) throw new RuntimeException('Live key changed');
    foreach ($backup['files'] as $record) {
        $filename = $record['file'] ?? '';
        if (!in_array($filename, ['database.sql.enc', 'application-files.tar.gz.enc'], true)) {
            throw new RuntimeException('Unexpected backup file');
        }
        $file = dirname($manifestPath).'/'.$filename;
        if (is_link($file) || !is_file($file) || !hash_equals($record['sha256'] ?? '', hash_file('sha256', $file))) {
            throw new RuntimeException('Backup integrity failure');
        }
    }
    echo "PASS: recent independent encrypted production backup checked\n";

    // Use private storage outside every public website document root.
    $parent = $home.'/jwtc-private-workhub-rollbacks';
    if (is_link($parent) || (!is_dir($parent) && !mkdir($parent, 0700))) {
        throw new RuntimeException('Private rollback directory unavailable');
    }
    $rollback = $parent.'/'.$sha;
    if (file_exists($rollback) || is_link($rollback)) throw new RuntimeException('Release already attempted');
    if (!mkdir($rollback, 0700)) throw new RuntimeException('Cannot allocate rollback');
    $prepared = $rollback.'/prepared';
    $previous = $rollback.'/previous';
    mkdir($prepared, 0700);
    mkdir($previous, 0700);
    $phase = 'preparing';
    $run(['/bin/tar', '-xzf', $package, '--directory='.$prepared, '--no-same-owner'], $root);
    foreach ($allowed as $relative) {
        $staged = $prepared.'/'.$relative;
        if (!is_file($staged) || is_link($staged)
            || !hash_equals($hashes[$relative], hash_file('sha256', $staged))) {
            throw new RuntimeException('Release file content mismatch');
        }
        $live = $root.'/'.$relative;
        $parentPath = realpath(dirname($live));
        if (!$parentPath || ($parentPath !== $root && !str_starts_with($parentPath, $root.'/'))
            || is_link($live)) throw new RuntimeException('Unsafe destination');
        if (!is_file($live) && $relative !== 'app/Services/WorkHubLinkImporter.php') {
            throw new RuntimeException('Existing release file unavailable');
        }
        if (is_file($live)) {
            $old = $previous.'/'.$relative;
            if (!is_dir(dirname($old))) mkdir(dirname($old), 0700, true);
            if (!copy($live, $old)) throw new RuntimeException('Cannot retain previous code');
        }
        if (str_ends_with($relative, '.php')) $run([PHP_BINARY, '-l', $staged], $root);
    }

    $phase = 'promoting';
    foreach ($allowed as $relative) {
        $live = $root.'/'.$relative;
        $temporary = $live.'.jbtc-'.$sha.'.tmp';
        if (file_exists($temporary) || is_link($temporary)) throw new RuntimeException('Conflicting temporary code file');
        if (!copy($prepared.'/'.$relative, $temporary)) throw new RuntimeException('Cannot prepare live file');
        chmod($temporary, 0644);
        if (!rename($temporary, $live)) throw new RuntimeException('Cannot promote live file');
        $changed[] = $relative;
    }

    $phase = 'application-verification';
    $run([PHP_BINARY, 'artisan', 'route:clear'], $root);
    $routes = $run([PHP_BINARY, 'artisan', 'route:list', '--except-vendor', '--json'], $root);
    if (!str_contains($routes, 'admin.external-jobs.import-link')) throw new RuntimeException('New route missing');
    $run([PHP_BINARY, 'artisan', 'view:cache'], $root);
    foreach (['/' => 200, '/search-jobs' => 200, '/admin/external-jobs' => 302] as $path => $expected) {
        $actual = trim($run(['/usr/bin/curl', '--silent', '--show-error', '--max-time', '20',
            '--output', '/dev/null', '--write-out', '%{http_code}', 'https://www.joingallagher.uk'.$path], $root, 30));
        if ((int) $actual !== $expected) throw new RuntimeException('Live HTTP smoke check failed');
    }
    file_put_contents($rollback.'/release-success.txt',
        'Deployed '.$sha.' at '.gmdate('c').'; fresh backup: '.($backup['backup_id'] ?? 'unknown')."\n", LOCK_EX);
    echo "PASS: Work Hub link importer deployed; route, templates and live HTTP checks passed\n";
    echo "WORKHUB_FEATURE_COMMIT ".$sha."\n";
    echo "WORKHUB_BACKUP_ID ".($backup['backup_id'] ?? 'unknown')."\n";
    echo "INFO: previous code retained privately; no job or applicant data changed by this promotion\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: Work Hub release stopped at '.$phase."\n");
    if ($changed && $rollback) {
        try {
            foreach (array_reverse($changed) as $relative) {
                $live = $root.'/'.$relative;
                $saved = $rollback.'/previous/'.$relative;
                if (is_file($saved)) {
                    if (!copy($saved, $live)) throw new RuntimeException('Cannot restore prior file');
                } elseif ($relative === 'app/Services/WorkHubLinkImporter.php') {
                    if (is_file($live) && !unlink($live)) throw new RuntimeException('Cannot restore missing prior service');
                } else {
                    throw new RuntimeException('Previous file unavailable');
                }
            }
            $run([PHP_BINARY, 'artisan', 'route:clear'], $root);
            $run([PHP_BINARY, 'artisan', 'view:clear'], $root);
            echo "PASS: previous code restored after failed check\n";
        } catch (Throwable $restoreError) {
            fwrite(STDERR, "STOP: rollback needs manual verification\n");
        }
    }
    exit(1);
}
