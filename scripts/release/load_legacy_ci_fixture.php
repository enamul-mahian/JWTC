<?php

/**
 * Loads ONLY the schema-only historic fixture into ephemeral GitHub CI MariaDB.
 * Do not run on production, staging or another MySQL database.
 */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$expectedDb = 'jwtc_legacy_ci_only';

if (getenv('JWTC_LEGACY_IMPORT_FIXTURE') !== 'ephemeral-only'
    || config('app.env') !== 'testing'
    || config('database.default') !== 'mysql'
    || config('database.connections.mysql.database') !== $expectedDb
    || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('app.url') !== 'https://ci.example.invalid') {
    fwrite(STDERR, "BLOCKED: Fixture loader requires disposable GitHub CI MySQL only.\n");
    exit(2);
}

$sql = file_get_contents(dirname(__DIR__, 2) . '/tests/fixtures/legacy_schema_only.sql');
if ($sql === false || strlen($sql) < 15000) {
    fwrite(STDERR, "BLOCKED: Schema-only fixture absent or incomplete.\n");
    exit(2);
}

$sql = preg_replace('/^\s*--[^\r\n]*(?:\r?\n|$)/m', '', $sql);
$statements = preg_split('/;\s*\n\s*\n/', trim($sql));
if (!$statements || count($statements) < 145) {
    fwrite(STDERR, "BLOCKED: Expected legacy DDL statements were not found.\n");
    exit(2);
}
foreach ($statements as $rawStatement) {
    $statement = trim(rtrim(trim($rawStatement), ';'));
    if ($statement === '') continue;
    $allowed = preg_match('/\A(?:CREATE TABLE\s+|ALTER TABLE\s+|SET FOREIGN_KEY_CHECKS\s*=|INSERT INTO \x60migrations\x60\s*)/i', $statement);
    if (!$allowed || preg_match('/\b(?:DELETE FROM|DROP TABLE|TRUNCATE)\b/i', $statement)) {
        fwrite(STDERR, "BLOCKED: Disallowed SQL detected in fixture.\n");
        exit(2);
    }
}

try {
    $connection = DB::connection('mysql');
    $activeDb = $connection->selectOne('SELECT DATABASE() AS db');
    if (($activeDb->db ?? null) !== $expectedDb) {
        fwrite(STDERR, "BLOCKED: Connected schema is not the disposable fixture database.\n");
        exit(2);
    }
    $pdo = $connection->getPdo();
    foreach ($statements as $rawStatement) {
        $statement = trim(rtrim(trim($rawStatement), ';'));
        if ($statement !== '') $pdo->exec($statement);
    }
    $tableCount = count($connection->select('SHOW TABLES'));
    $migrationCount = (int) $connection->table('migrations')->count();
    if ($tableCount !== 46 || $migrationCount !== 43) {
        fwrite(STDERR, "BLOCKED: Legacy fixture table/migration counts differ.\n");
        exit(3);
    }
    echo "PASS: Legacy schema-only fixture contains 46 tables and 43 applied migration markers.\n";
} catch (Throwable $exception) {
    // Suppress database error message, SQL text and credentials in logs.
    fwrite(STDERR, "FAIL: Schema-only fixture import error type: " . get_class($exception) . "\n");
    exit(3);
}
