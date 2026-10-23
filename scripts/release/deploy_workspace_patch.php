<?php
// Code-only release: no migrations, customer writes, credentials or upload changes.
ini_set('display_errors', '0');
$phase = 'preflight'; $changed = []; $maintenance = false; $backup = null;
$root = realpath('.'); $home = realpath(getenv('HOME'));
$run = static function (array $args) use ($root): string {
    $pipes = [];
    $p = proc_open($args, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes, $root);
    if (!is_resource($p)) throw new RuntimeException('Cannot start release command');
    fclose($pipes[0]); $out = stream_get_contents($pipes[1]); stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($p) !== 0) throw new RuntimeException('Release command failed');
    return $out;
};
try {
    if ($root !== $home.'/joingallagher.uk' || !is_file($root.'/artisan')) throw new RuntimeException('Unexpected live root');
    $bundle = json_decode(stream_get_contents(STDIN), true, 64, JSON_THROW_ON_ERROR);
    if (!preg_match('/^[a-f0-9]{40}$/D', $bundle['commit'] ?? '') || count($bundle['files'] ?? []) < 10) throw new RuntimeException('Invalid bundle');
    $envHash = hash_file('sha256', $root.'/.env');
    $parent = $home.'/jwtc-private-deployments';
    if (!is_dir($parent) || is_link($parent) || realpath($parent) !== $parent) throw new RuntimeException('Private rollback path unavailable');
    $lock = fopen($parent.'/workspace-release.lock','c');
    if (!$lock || !flock($lock, LOCK_EX|LOCK_NB)) throw new RuntimeException('Another release is running');
    $id = gmdate('Ymd-His').'-workspace-'.bin2hex(random_bytes(4));
    $backup = $parent.'/'.$id; mkdir($backup,0700); mkdir($backup.'/previous',0700); mkdir($backup.'/prepared',0700);
    foreach ($bundle['files'] as $path=>$record) {
        if (!preg_match('#^(app/(Http/(Controllers|Middleware)/|Services/|Helpers\.php)|resources/views/|public/theme/)#D', $path)
            || str_contains($path,'..') || str_contains($path,'\\') || !preg_match('/\.(php|css|js)$/D',$path)) throw new RuntimeException('Invalid release path');
        $bytes = base64_decode($record['content'],true);
        if ($bytes === false || !hash_equals($record['sha256'],hash('sha256',$bytes))) throw new RuntimeException('Bundle hash mismatch');
        $target = $root.'/'.$path;
        for ($dir=dirname($target);$dir!==$root;$dir=dirname($dir)) if (is_link($dir)) throw new RuntimeException('Symlink parent');
        if (is_link($target)) throw new RuntimeException('Symlink target');
        $prepared = $backup.'/prepared/'.$path;
        if (!is_dir(dirname($prepared))) mkdir(dirname($prepared),0700,true);
        file_put_contents($prepared,$bytes,LOCK_EX);
        if (str_ends_with($path,'.php') && !str_ends_with($path,'.blade.php')) $run([PHP_BINARY,'-l',$prepared]);
        $previous = $backup.'/previous/'.$path;
        if (is_file($target)) { if (!is_dir(dirname($previous))) mkdir(dirname($previous),0700,true); if (!copy($target,$previous)) throw new RuntimeException('Backup failed'); }
    }
    $verify = base64_decode($bundle['verification'],true);
    if ($verify === false || !hash_equals($bundle['verification_sha256'],hash('sha256',$verify))) throw new RuntimeException('Verification hash mismatch');
    file_put_contents($backup.'/verify.php',$verify);
    $run([PHP_BINARY,'-l',$backup.'/verify.php']);
    $phase = 'maintenance'; $run([PHP_BINARY,'artisan','down','--retry=10']); $maintenance = true;
    $phase = 'promote';
    foreach ($bundle['files'] as $path=>$record) {
        $target=$root.'/'.$path; $changed[$path]=is_file($target);
        if (!is_dir(dirname($target))) mkdir(dirname($target),0755,true);
        $temp=$target.'.workspace-'.$id;
        if (!copy($backup.'/prepared/'.$path,$temp)) throw new RuntimeException('Prepare failed');
        chmod($temp,0644); if (!rename($temp,$target)) throw new RuntimeException('Promote failed');
        if (!hash_equals($record['sha256'],hash_file('sha256',$target))) throw new RuntimeException('Promoted bytes mismatch');
        if (function_exists('opcache_invalidate')) opcache_invalidate($target,true);
    }
    $phase = 'templates'; $run([PHP_BINARY,'artisan','view:clear']); $run([PHP_BINARY,'artisan','view:cache']);
    $phase = 'authenticated-read-only-verification'; echo $run([PHP_BINARY,$backup.'/verify.php']);
    if (!hash_equals($envHash,hash_file('sha256',$root.'/.env'))) throw new RuntimeException('Environment changed');
    $phase = 'return-live'; $run([PHP_BINARY,'artisan','up']); $maintenance=false;
    $phase = 'live-synthetic-browser-fixtures';
    $generator=base64_decode($bundle['fixture_generator']??'',true);
    if($generator===false||!hash_equals($bundle['fixture_generator_sha256']??'',hash('sha256',$generator)))throw new RuntimeException('Fixture generator mismatch');
    // Resolve application bootstrap from the live root, not the private backup directory.
    $generator=str_replace("__DIR__.'/../../",var_export($root.'/',true).".'",$generator);
    file_put_contents($backup.'/render-ui.php',$generator);
    putenv('JWTC_UI_FIXTURES='.$backup.'/ui-fixtures');
    echo $run([PHP_BINARY,$backup.'/render-ui.php']);
    $phase = 'http';
    foreach (['/','/jobseeker/login','/theme/workspace.css?v=20261024-3','/theme/workspace.js?v=20261024-3'] as $path) {
        $body=$run(['/usr/bin/curl','--fail','--silent','--show-error','--max-time','25','--header','Cache-Control: no-cache','https://joingallagher.uk'.$path]);
        if (str_starts_with($path,'/theme/')) {
            $file='public'.explode('?',$path)[0];
            if (!hash_equals($bundle['files'][$file]['sha256'],hash('sha256',$body))) throw new RuntimeException('Served asset mismatch');
        }
        echo 'LIVE_HTTP '.$path." OK\n";
    }
    try { echo 'SERVER_NODE '.trim($run(['node','--version']))."\n"; } catch (Throwable $ignore) { echo "SERVER_NODE unavailable; application runtime is PHP\n"; }
    echo 'SERVER_PHP '.PHP_VERSION."\n";
    file_put_contents($backup.'/deployment-report.json',json_encode(['commit'=>$bundle['commit'],'id'=>$id,'files'=>array_keys($bundle['files']),'deployed_at_utc'=>gmdate('c'),'env_unchanged'=>true,'database_migrations_run'=>false,'read_only_views_passed'=>true],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    echo 'DEPLOYED_COMMIT '.$bundle['commit']."\nDEPLOYMENT_ID ".$id."\nPASS: code-only workspace release, previous code retained privately\n";
} catch (Throwable $error) {
    fwrite(STDERR,'FAIL workspace release at '.$phase."; private details suppressed\n");
    if ($changed) {
        try {
            if (!$maintenance) { $run([PHP_BINARY,'artisan','down','--retry=10']); $maintenance=true; }
            foreach ($changed as $path=>$existed) {
                if ($existed) { if(!copy($backup.'/previous/'.$path,$root.'/'.$path)) throw new RuntimeException('Rollback copy failed'); chmod($root.'/'.$path,0644); }
                elseif (is_file($root.'/'.$path)) unlink($root.'/'.$path);
            }
            $run([PHP_BINARY,'artisan','view:clear']); $run([PHP_BINARY,'artisan','up']); $maintenance=false;
            echo "ROLLBACK: previous code restored\n";
        } catch(Throwable $ignored) { fwrite(STDERR,"Rollback incomplete; private backup retained\n"); }
    } elseif ($maintenance) { try {$run([PHP_BINARY,'artisan','up']);}catch(Throwable $ignored){} }
    exit(1);
}
