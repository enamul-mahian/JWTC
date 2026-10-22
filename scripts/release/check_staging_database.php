<?php

// Read-only staging identity check. Never bootstrap Laravel, run migrations,
// create accounts, change .env, or print connection values/private data.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
set_error_handler(static function () { throw new RuntimeException('Read failed'); });

try {
    $input = json_decode(stream_get_contents(STDIN), true, 8, JSON_THROW_ON_ERROR);
    if (!is_array($input) || ($input['database'] ?? '') !== 'dropoqux_jwtc_stage'
        || ($input['username'] ?? '') !== 'dropoqux_jwtc_stage'
        || !is_string($input['password'] ?? null) || $input['password'] === '') {
        throw new RuntimeException('Unexpected staging identity');
    }
    if (!is_file('artisan') || !is_file('.env') || !is_file('vendor/autoload.php')) {
        throw new RuntimeException('Laravel root or dependencies unavailable');
    }
    require 'vendor/autoload.php';
    if (is_file('bootstrap/cache/config.php')) {
        $config = require 'bootstrap/cache/config.php';
        $driver = $config['database']['default'] ?? '';
        $db = $config['database']['connections'][$driver] ?? [];
        // URL connections need independent resolution; do not guess their DB.
        if ($driver !== 'mysql' || !empty($db['url'])) {
            throw new RuntimeException('Unsupported cached database configuration');
        }
        $liveDatabase = $db['database'] ?? '';
        $host = $db['host'] ?? '127.0.0.1';
        $port = $db['port'] ?? '3306';
        $socket = $db['unix_socket'] ?? '';
        $ssl = $db['options'] ?? [];
    } else {
        $env = Dotenv\Dotenv::parse(file_get_contents('.env'));
        $value = static function ($key, $fallback = '') use ($env) {
            $process = getenv($key);
            return $process !== false ? $process : ($env[$key] ?? $fallback);
        };
        if ($value('DB_CONNECTION', 'mysql') !== 'mysql' || $value('DATABASE_URL') !== ''
            || $value('DB_URL') !== '') {
            throw new RuntimeException('Unsupported database configuration');
        }
        $liveDatabase = $value('DB_DATABASE');
        $host = $value('DB_HOST', '127.0.0.1');
        $port = $value('DB_PORT', '3306');
        $socket = $value('DB_SOCKET');
        $ssl = [];
        if ($value('MYSQL_ATTR_SSL_CA') !== '') {
            $ssl[PDO::MYSQL_ATTR_SSL_CA] = $value('MYSQL_ATTR_SSL_CA');
        }
    }
    if (!is_string($liveDatabase) || $liveDatabase === ''
        || $liveDatabase === $input['database'] || str_contains($liveDatabase, '${')) {
        throw new RuntimeException('Cannot establish independent staging database');
    }
    if (!is_string($host) || !preg_match('/^[A-Za-z0-9_.:\\-]+$/D', $host)
        || !preg_match('/^[0-9]{1,5}$/D', (string) $port)
        || (int) $port < 1 || (int) $port > 65535
        || !is_string($socket) || str_contains($socket, ';')) {
        throw new RuntimeException('Unsupported database host');
    }
    $dsn = $socket !== '' ? 'mysql:unix_socket='.$socket : 'mysql:host='.$host.';port='.$port;
    $dsn .= ';dbname='.$input['database'].';charset=utf8mb4';
    // Preserve only SSL trust material; never inherit live credentials or a
    // configured init command that could execute arbitrary SQL on connection.
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_EMULATE_PREPARES => false];
    foreach ([PDO::MYSQL_ATTR_SSL_CA, PDO::MYSQL_ATTR_SSL_CERT, PDO::MYSQL_ATTR_SSL_KEY,
              PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] as $key) {
        if (array_key_exists($key, $ssl)) $options[$key] = $ssl[$key];
    }
    $pdo = new PDO($dsn, $input['username'], $input['password'], $options);
    $pdo->exec('SET SESSION TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    $actual = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if ($actual !== $input['database'] || $actual === $liveDatabase) {
        throw new RuntimeException('Database identity mismatch');
    }
    $query = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
    $query->execute([$actual]);
    $tables = (int) $query->fetchColumn();
    $pdo->rollBack();
    echo "PASS: staging credentials accepted; database differs from live configuration\n";
    if ($tables !== 0) {
        echo "STOP: staging database contains tables; no overwrite or restore attempted\n";
        exit(2);
    }
    echo "PASS: staging database is empty\n";
    echo "INFO: no data copied, migrations run, backups created or application deployed\n";
} catch (Throwable $error) {
    // Deliberately suppress PDO messages, DB host/schema, password and paths.
    fwrite(STDERR, "FAIL: staging identity/connection check failed; values suppressed\n");
    exit(1);
}
