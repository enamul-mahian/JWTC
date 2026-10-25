<?php
// Strictly read-only: verify retained encrypted snapshot before Stage2 restore.
ini_set('display_errors', '0');
try {
    $home = realpath(getenv('HOME') ?: '');
    $root = realpath('.');
    if (!$home || $root !== $home.'/joingallagher.uk') throw new RuntimeException('Unexpected application root');
    $id = '20261025-033245-808519b6';
    $base = $home.'/jwtc-private-backups/'.$id;
    $keys = $home.'/jwtc-backup-keys';
    if (is_link($base) || realpath($base)!==$base || is_link($keys) || realpath($keys)!==$keys) throw new RuntimeException('Backup missing or unsafe');
    $m = json_decode(file_get_contents($base.'/manifest.json'),true,64,JSON_THROW_ON_ERROR);
    if (($m['backup_id']??'')!==$id || ($m['files_stable_during_snapshot']??false)!==true || count($m['files']??[])!==2) throw new RuntimeException('Invalid manifest');
    if (!in_array(($m['consistency_method']??''),['single-transaction','all-base-tables-read-lock'],true)) throw new RuntimeException('Unverified snapshot consistency');
    foreach ($m['files'] as $record) {
        $name=$record['file']??'';
        if (!in_array($name,['database.sql.enc','application-files.tar.gz.enc'],true)) throw new RuntimeException('Unexpected backup asset');
        $path=$base.'/'.$name;
        if (is_link($path) || !is_file($path) || !hash_equals($record['sha256']??'',hash_file('sha256',$path))) throw new RuntimeException('Encrypted asset checksum mismatch');
    }
    $appKey=$keys.'/'.$id.'.app-key';
    if (is_link($appKey) || !is_file($appKey) || !hash_equals($m['app_key_sha256']??'',hash_file('sha256',$appKey))) throw new RuntimeException('Application key escrow mismatch');
    echo "PASS: retained encrypted production snapshot and APP_KEY hashes verified\n";
    echo "SNAPSHOT_METHOD ".$m['consistency_method']."\n";
    echo "INFO: no restore or database writes attempted\n";
} catch (Throwable $e) {
    fwrite(STDERR,"FAIL: Stage2 backup readiness blocked; private paths suppressed\n");
    exit(1);
}
