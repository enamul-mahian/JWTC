<?php
// Remove only regenerable unpacked CI packages with an intact verified archive.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
set_error_handler(static function () { throw new RuntimeException('Filesystem operation failed'); });
try {
    $home = realpath(getenv('HOME'));
    $base = $home.'/jwtc-private-staging/20261022-215145-f92ca979';
    if (realpath('.') !== $home.'/joingallagher.uk' || realpath($base) !== $base || is_link($base)) throw new RuntimeException('Wrong root');
    $removed = 0;
    foreach (glob($base.'/candidate-input*', GLOB_ONLYDIR) as $input) {
        if (is_link($input) || realpath($input) !== $input || !is_file($input.'/release-manifest.json')) continue;
        $manifest = json_decode(file_get_contents($input.'/release-manifest.json'), true, 64, JSON_THROW_ON_ERROR);
        if (!preg_match('/^jwtc-[a-f0-9]{40}\.tar\.gz$/D', $manifest['package'] ?? '') || !hash_equals($manifest['sha256'], hash_file('sha256', $input.'/'.$manifest['package']))) throw new RuntimeException('Retained archive verification failed');
        $expected = [];
        foreach ($manifest['files'] as $record) $expected[$record['path']] = $record['sha256'];
        $unpacked = str_replace('/candidate-input', '/candidate-unpacked', $input);
        $targets = $input === $base.'/candidate-input-portal-polish' ? glob($unpacked.'-incomplete-*', GLOB_ONLYDIR) : [$unpacked];
        foreach ($targets as $target) {
            if (!is_dir($target)) continue;
            if (is_link($target) || realpath($target) !== $target || !str_starts_with($target, $base.'/candidate-unpacked')) throw new RuntimeException('Unsafe generated directory');
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            $files = 0;
            foreach ($iterator as $entry) {
                if ($entry->isLink()) throw new RuntimeException('Generated package symlink refused');
                if ($entry->isDir()) continue;
                $relative = substr($entry->getPathname(), strlen($target) + 1);
                if ($relative !== '.extraction-complete' && (!isset($expected[$relative]) || !hash_equals($expected[$relative], hash_file('sha256', $entry->getPathname())))) throw new RuntimeException('Unrecognized generated package file');
                $files++;
            }
            foreach ($iterator as $entry) {
                if ($entry->isDir()) rmdir($entry->getPathname());
                else unlink($entry->getPathname());
            }
            rmdir($target);
            $removed += $files;
            echo 'DEDUPLICATED_PACKAGE '.basename($target).' FILES '.$files."\n";
        }
    }
    // Retain complete rollback paths while sharing identical immutable vendor
    // bytes. Only dependency files with equal hashes/modes are linked. Never
    // touch application data, uploads, environment files or encryption keys.
    $linked = 0;
    $vendor = $home.'/joingallagher.uk/vendor';
    $targets = glob($home.'/jwtc-private-deployments/*/previous/vendor', GLOB_ONLYDIR);
    $targets[] = $base.'/application/vendor';
    $targets[] = $base.'/candidate-unpacked-portal-polish/vendor';
    foreach ($targets as $target) {
        if (!is_dir($target)) continue;
        if (is_link($target) || realpath($target) !== $target) throw new RuntimeException('Unsafe dependency directory');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $entry) {
            if (!$entry->isFile() || $entry->isLink()) continue;
            $relative = substr($entry->getPathname(), strlen($target) + 1);
            if (str_starts_with($relative, 'bin/')) continue;
            $source = $vendor.'/'.$relative;
            if (!is_file($source) || is_link($source) || realpath($source) !== $source) continue;
            $current = stat($source);
            if (in_array($target, [$base.'/application/vendor', $base.'/candidate-unpacked-portal-polish/vendor'], true)) { chmod($entry->getPathname(), $current['mode'] & 0777); clearstatcache(true, $entry->getPathname()); }
            $old = stat($entry->getPathname());
            if ($old['uid'] !== $current['uid'] || $old['gid'] !== $current['gid'] || $old['dev'] !== $current['dev'] || $old['ino'] === $current['ino'] || $old['mode'] !== $current['mode'] || $old['size'] !== $current['size']) continue;
            $hash = hash_file('sha256', $source);
            if (!hash_equals($hash, hash_file('sha256', $entry->getPathname()))) continue;
            $temporary = $entry->getPathname().'.jwtc-share';
            if (file_exists($temporary) || is_link($temporary)) throw new RuntimeException('Dependency temporary path exists');
            if (!link($source, $temporary)) throw new RuntimeException('Dependency sharing unavailable');
            if (!hash_equals($hash, hash_file('sha256', $temporary))) throw new RuntimeException('Dependency sharing mismatch');
            if (!rename($temporary, $entry->getPathname())) throw new RuntimeException('Cannot retain shared dependency');
            $linked++;
        }
    }
    echo 'PASS: identical dependency files shared without removing rollback paths; files '.$linked."\n";
    echo 'PASS: removed '.$removed." redundant extracted files; verified archives, backups, application and uploads retained\n";
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: generated package deduplication stopped; private details suppressed\n");
    exit(1);
}
