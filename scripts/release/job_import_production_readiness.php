<?php
// Read-only approval gate for the exact staged job-import release.
ini_set('display_errors','0');
try {
    $home = realpath(getenv('HOME') ?: '');
    $root = realpath('.');
    if (!$home || $root !== $home.'/joingallagher.uk') throw new RuntimeException('Unexpected production root');
    require $root.'/vendor/autoload.php';
    $base=$home.'/jwtc-private-staging/20261025-033245-808519b6-stage2';
    if (realpath($base)!==$base || is_link($base)) throw new RuntimeException('Missing safe Stage2');
    $report=json_decode(file_get_contents($base.'/candidate-job-import-verification.json'),true,64,JSON_THROW_ON_ERROR);
    $manifest=json_decode(file_get_contents($base.'/candidate-input-job-import/release-manifest.json'),true,64,JSON_THROW_ON_ERROR);
    if (($report['candidate_commit']??'')!=='ffe1b9b1f27e236d07026df607b0ccc3d9d2b7f8'
      || ($manifest['commit']??'')!==$report['candidate_commit']
      || ($report['production_modified']??true)!==false
      || ($report['historical_tables_verified']??0)<46
      || ($report['migrations']??0)<60) throw new RuntimeException('Stage2 verification mismatch');
    $env=Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    if (($env['DB_DATABASE']??'')==='dropoqux_jwtc_stage2') throw new RuntimeException('Live database points to Stage2');
    $host=$env['DB_HOST']??'127.0.0.1'; $port=$env['DB_PORT']??'3306';
    $dsn='mysql:host='.$host.';port='.$port.';dbname='.$env['DB_DATABASE'].';charset=utf8mb4';
    $pdo=new PDO($dsn,$env['DB_USERNAME'],$env['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('SET SESSION TRANSACTION READ ONLY');
    $migrations=$pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
    foreach (['2026_10_25_000001_create_external_job_imports_table','2026_10_25_000002_add_optional_external_job_details'] as $name) {
        if (in_array($name,$migrations,true)) throw new RuntimeException('Production migration already present; require review');
    }
    $backup=$home.'/jwtc-private-backups/20261025-033245-808519b6';
    if (is_link($backup) || realpath($backup)!==$backup) throw new RuntimeException('Backup unavailable');
    $m=json_decode(file_get_contents($backup.'/manifest.json'),true,64,JSON_THROW_ON_ERROR);
    if (($m['backup_id']??'')!=='20261025-033245-808519b6') throw new RuntimeException('Backup identity mismatch');
    foreach ($m['files']??[] as $record) {
        if (!in_array(($record['file']??''),['database.sql.enc','application-files.tar.gz.enc'],true)) throw new RuntimeException('Backup asset mismatch');
        $f=$backup.'/'.$record['file'];
        if (is_link($f)||!hash_equals($record['sha256']??'',hash_file('sha256',$f))) throw new RuntimeException('Backup hash mismatch');
    }
    echo "PASS: verified Stage2 job-import candidate and unchanged legacy production migration state\n";
    echo "PASS: retained encrypted backup hashes; production read-only checks\n";
    echo "INFO: historical backup alone does not establish that it is fresh enough for production deployment\n";
} catch (Throwable $e) {
    fwrite(STDERR,"STOP: production release preflight failed; no writes performed\n");
    exit(1);
}
