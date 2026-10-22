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
        $targets = $input === $base.'/candidate-input-approved' ? glob($unpacked.'-incomplete-*', GLOB_ONLYDIR) : [$unpacked];
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
    echo 'PASS: removed '.$removed." redundant extracted files; verified archives, backups, application and uploads retained\n";
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: generated package deduplication stopped; private details suppressed\n");
    exit(1);
}
