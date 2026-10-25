<?php

// Backup-gated emergency UI promotion: code/assets only, no SQL or migrations.
// This installer is run over trusted SSH by the dedicated GitHub workflow.
ini_set('display_errors', '0');
ini_set('log_errors', '0');

$phase = 'preflight';
$changed = [];
$rollback = null;
$root = realpath('.');
$envHash = null;

try {
    if (!$root || !is_file($root.'/vendor/autoload.php')) {
        throw new RuntimeException('Missing Laravel runtime');
    }
    require_once $root.'/vendor/autoload.php';
    $home = realpath(getenv('HOME') ?: '');
    if (!$home || $root !== $home.'/joingallagher.uk' || is_link($root)) {
        throw new RuntimeException('Unexpected live application root');
    }
    $envHash = hash_file('sha256', $root.'/.env');
    if (!$envHash) throw new RuntimeException('Missing environment fingerprint');

    $execute = static function (array $command, int $timeout = 120) use ($root): string {
        $process = new Symfony\Component\Process\Process($command, $root);
        $process->setTimeout($timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException('Release validation command failed');
        }
        return $process->getOutput();
    };

    $payload = json_decode(stream_get_contents(STDIN), true, 12, JSON_THROW_ON_ERROR);
    $sha = $payload['release_sha'] ?? '';
    if (!is_string($sha) || !preg_match('/^[a-f0-9]{40}$/D', $sha)) {
        throw new RuntimeException('Invalid commit identity');
    }
    $allowed = [
        'app/Http/Controllers/Admin/DashboardController.php',
        'app/Http/Controllers/Admin/AdminUserActionsController.php',
        'app/Services/AdminUserPurgeService.php',
        'app/Exceptions/Handler.php',
        'routes/web.php',
        'resources/views/auth/logout-confirm.blade.php',
        'resources/views/admin/userManagement/index.blade.php',
        'resources/views/admin/userManagement/account_actions.blade.php',
        'resources/views/admin/userCreate/create.blade.php',
        'resources/views/admin/userCreate/edit.blade.php',
    ];
    $newFiles = [
        'app/Http/Controllers/Admin/AdminUserActionsController.php',
        'app/Services/AdminUserPurgeService.php',
        'resources/views/auth/logout-confirm.blade.php',
        'resources/views/admin/userManagement/account_actions.blade.php',
    ];
    $hashes = $payload['hashes'] ?? [];
    if (!is_array($hashes) || count($hashes) !== count($allowed)) {
        throw new RuntimeException('Incorrect release file count');
    }
    foreach ($allowed as $file) {
        if (!isset($hashes[$file]) || !is_string($hashes[$file])
            || !preg_match('/^[a-f0-9]{64}$/D', $hashes[$file])) {
            throw new RuntimeException('Invalid release file fingerprint');
        }
    }
    if (array_diff(array_keys($hashes), $allowed)) {
        throw new RuntimeException('Unexpected release file');
    }
    $archive = $home.'/jwtc-private-workhub-incoming/'.$sha.'/admin-user-actions-release.tar.gz';
    if (!is_file($archive) || is_link($archive)
        || !hash_equals((string) ($payload['archive_sha256'] ?? ''), hash_file('sha256', $archive))) {
        throw new RuntimeException('Signed release archive unavailable or corrupted');
    }
    $actual = explode("\n", trim($execute(['/bin/tar', '-tzf', $archive])));
    $expected = $allowed;
    sort($actual);
    sort($expected);
    if ($actual !== $expected) throw new RuntimeException('Unexpected archive contents');

    // Fresh, independent encrypted website+database backup, created by the
    // immediately preceding workflow step. This deployment itself does not write DB.
    $manifests = glob($home.'/jwtc-private-backups/*/manifest.json');
    rsort($manifests, SORT_STRING);
    $manifest = $manifests[0] ?? null;
    if (!$manifest || !is_file($manifest)) throw new RuntimeException('Fresh encrypted backup missing');
    $snapshot = json_decode(file_get_contents($manifest), true, 64, JSON_THROW_ON_ERROR);
    $age = time() - strtotime((string) ($snapshot['created_at_utc'] ?? '1970-01-01'));
    if ($age < -120 || $age > 900 || ($snapshot['files_stable_during_snapshot'] ?? false) !== true
        || count($snapshot['files'] ?? []) !== 2) {
        throw new RuntimeException('Encrypted backup is stale or incomplete');
    }
    $dotenv = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    if (!hash_equals((string) ($snapshot['app_key_sha256'] ?? ''), hash('sha256', (string) ($dotenv['APP_KEY'] ?? '')))) {
        throw new RuntimeException('Backup environment identity mismatch');
    }
    foreach ($snapshot['files'] as $record) {
        $name = $record['file'] ?? '';
        if (!in_array($name, ['database.sql.enc', 'application-files.tar.gz.enc'], true)
            || !is_file(dirname($manifest).'/'.$name)
            || is_link(dirname($manifest).'/'.$name)
            || !hash_equals((string) ($record['sha256'] ?? ''), hash_file('sha256', dirname($manifest).'/'.$name))) {
            throw new RuntimeException('Encrypted backup hash check failed');
        }
    }
    echo "BACKUP_VERIFIED\n";

    $parent = $home.'/jwtc-private-workhub-rollbacks';
    if (is_link($parent) || (!is_dir($parent) && !mkdir($parent, 0700))) {
        throw new RuntimeException('No safe private rollback folder');
    }
    $rollback = $parent.'/admin-user-actions-'.$sha;
    if (file_exists($rollback) || is_link($rollback) || !mkdir($rollback, 0700)) {
        throw new RuntimeException('Release already attempted or cannot retain rollback');
    }
    mkdir($rollback.'/prepared', 0700);
    mkdir($rollback.'/previous', 0700);
    $phase = 'preparation';
    $execute(['/bin/tar', '-xzf', $archive, '--directory='.$rollback.'/prepared', '--no-same-owner']);
    foreach ($allowed as $relative) {
        $prepared = $rollback.'/prepared/'.$relative;
        if (!is_file($prepared) || is_link($prepared)
            || !hash_equals($hashes[$relative], hash_file('sha256', $prepared))) {
            throw new RuntimeException('Prepared file checksum mismatch');
        }
        $target = $root.'/'.$relative;
        $directory = realpath(dirname($target));
        if (!$directory || ($directory !== $root && !str_starts_with($directory, $root.'/'))
            || is_link($target)) {
            throw new RuntimeException('Unsafe destination');
        }
        if (!is_file($target) && !in_array($relative, $newFiles, true)) {
            throw new RuntimeException('Existing template missing');
        }
        if (is_file($target)) {
            $saved = $rollback.'/previous/'.$relative;
            if (!is_dir(dirname($saved))) mkdir(dirname($saved), 0700, true);
            if (!copy($target, $saved)) throw new RuntimeException('Cannot retain previous template');
        }
    }
    $routes = file_get_contents($rollback.'/prepared/routes/web.php');
    $controller = file_get_contents($rollback.'/prepared/app/Http/Controllers/Admin/AdminUserActionsController.php');
    $service = file_get_contents($rollback.'/prepared/app/Services/AdminUserPurgeService.php');
    $form = file_get_contents($rollback.'/prepared/resources/views/admin/userManagement/account_actions.blade.php');
    if (!str_contains($routes, "name('admin.users.destroy')")
        || !str_contains($routes, "name('admin.users.password')")
        || !str_contains($controller, "current_password")
        || !str_contains($controller, "confirm_email")
        || !str_contains($service, "private-documents/")
        || !str_contains($form, "@method('DELETE')")
        || !str_contains($form, '@csrf')) {
        throw new RuntimeException('Secure admin management release validation failed');
    }
    foreach ($allowed as $relative) {
        if (str_ends_with($relative, '.php')) {
            $execute([PHP_BINARY, '-l', $rollback.'/prepared/'.$relative]);
        }
    }
    $phase = 'atomic-promotion';
    foreach ($allowed as $relative) {
        $target = $root.'/'.$relative;
        $temp = $target.'.employer-release-'.$sha;
        if (file_exists($temp) || is_link($temp)
            || !copy($rollback.'/prepared/'.$relative, $temp)) {
            throw new RuntimeException('Cannot stage live file');
        }
        chmod($temp, 0644);
        if (!rename($temp, $target)) throw new RuntimeException('Cannot promote UI asset');
        $changed[] = $relative;
        if (!hash_equals($hashes[$relative], hash_file('sha256', $target))) {
            throw new RuntimeException('Live file fingerprint mismatch');
        }
        if (function_exists('opcache_invalidate')) opcache_invalidate($target, true);
    }
    $phase = 'laravel-view-compilation';
    $execute([PHP_BINARY, 'artisan', 'route:clear']);
    $execute([PHP_BINARY, 'artisan', 'view:clear']);
    $execute([PHP_BINARY, 'artisan', 'view:cache'], 180);
    if (!hash_equals($envHash, hash_file('sha256', $root.'/.env'))) {
        throw new RuntimeException('Application environment changed');
    }

    $phase = 'live-http-verification';
    $base = 'https://joingallagher.uk';
    // Public smoke check confirms that the deployed shared route file is
    // available and its CSRF-protected sign-out confirmation is responsive.
    $confirmation = $execute(['/usr/bin/curl', '--location', '--fail', '--silent',
        '--show-error', '--max-time', '25', $base.'/logout/confirm'], 35);
    if (!str_contains($confirmation, 'Confirm sign out') ||
        !str_contains($confirmation, 'name="_token"')) {
        throw new RuntimeException('Live routing/CSRF smoke check failed');
    }
    $routesText = $execute([PHP_BINARY, 'artisan', 'route:list', '--path=dashboard/user', '--no-ansi']);
    if (!str_contains($routesText, 'admin.users.password') ||
        !str_contains($routesText, 'admin.users.destroy')) {
        throw new RuntimeException('User account administration routes missing');
    }
    $indexStatus = trim($execute(['/usr/bin/curl', '--location', '--silent', '--show-error',
        '--max-time', '25', '--output', '/dev/null', '--write-out', '%{http_code}', $base.'/'], 35));
    if ($indexStatus !== '200') throw new RuntimeException('Public homepage health check failed');
    echo "ADMIN_USER_ROUTES_VERIFIED\nLOGOUT_CONFIRM_200\nHOME_HTTP_200\n";
    file_put_contents($rollback.'/release-success.txt', 'Admin user actions '.$sha.' deployed '.gmdate('c')."\n", LOCK_EX);
    echo "ADMIN_USER_ACTIONS_DEPLOYED ".$sha."\n";
    echo "ROLLBACK_RETAINED ".basename($rollback)."\n";
    echo "INFO: no application data, databases, permissions or job records modified\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: admin user actions release at '.$phase."; details withheld\n");
    if ($changed && $rollback) {
        try {
            foreach (array_reverse($changed) as $relative) {
                $target = $root.'/'.$relative;
                $saved = $rollback.'/previous/'.$relative;
                if (is_file($saved)) {
                    if (!copy($saved, $target)) throw new RuntimeException('Rollback copy failed');
                    chmod($target, 0644);
                } elseif (is_file($target) && !unlink($target)) {
                    throw new RuntimeException('Rollback of added stylesheet failed');
                }
                if (function_exists('opcache_invalidate')) opcache_invalidate($target, true);
            }
            $execute([PHP_BINARY, 'artisan', 'view:clear']);
            $execute([PHP_BINARY, 'artisan', 'view:cache'], 180);
            echo "PREVIOUS_UI_RESTORED\n";
        } catch (Throwable $ignored) {
            fwrite(STDERR, "ROLLBACK_INCOMPLETE; previous files retained privately\n");
        }
    }
    exit(1);
}
