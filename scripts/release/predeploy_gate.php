<?php
ini_set('display_errors', '0');
ini_set('log_errors', '0');
$phase = 'identity';
$server = null;
try {
    $root = realpath('.');
    require $root.'/vendor/autoload.php';
    $home = realpath(getenv('HOME'));
    $base = $home.'/jwtc-private-staging/20261022-215145-f92ca979';
    $stage = $base.'/application';
    $report = json_decode(file_get_contents($base.'/candidate-security-verification.json'), true, 16, JSON_THROW_ON_ERROR);
    if (($report['migrations'] ?? 0) !== 58 || ($report['historical_tables_verified'] ?? 0) < 46) throw new RuntimeException('Unverified candidate');
    $run = static function (array $args, ?string $cwd = null, array $env = []): string {
        $p = new Symfony\Component\Process\Process($args, $cwd, $env);
        $p->setTimeout(120);
        $p->run();
        if (!$p->isSuccessful()) throw new RuntimeException('Gate command failed');
        return $p->getOutput();
    };
    $phase = 'domain';
    $uapi = null;
    foreach (['/usr/bin/uapi', '/bin/uapi', '/usr/local/bin/uapi', '/usr/local/cpanel/bin/uapi'] as $path) if (is_executable($path)) { $uapi = $path; break; }
    if (!$uapi) throw new RuntimeException('UAPI unavailable');
    $domain = json_decode($run([$uapi, '--output=json', 'DomainInfo', 'single_domain_data', 'domain=joingallagher.uk']), true, 64, JSON_THROW_ON_ERROR);
    $roots = [];
    $walk = static function ($data) use (&$walk, &$roots): void {
        foreach ((array) $data as $key => $value) {
            if (in_array($key, ['documentroot', 'document_root'], true) && is_string($value)) $roots[] = realpath($value);
            elseif (is_array($value)) $walk($value);
        }
    };
    $walk($domain['result']['data'] ?? []);
    $roots = array_values(array_unique($roots));
    echo 'DOCUMENT_ROOTS '.json_encode($roots)."\n";
    if (count($roots) !== 1 || !in_array($roots[0], [$root, $root.'/public'], true)) throw new RuntimeException('Unsupported web root');
    echo 'DOCUMENT_ROOT_LAYOUT '.($roots[0] === $root ? 'application-root' : 'public-subdirectory')."\n";
    echo 'ROOT_ENTRYPOINT '.(is_file($root.'/index.php') ? 'present' : 'absent')."\n";
    if (is_file($root.'/index.php')) {
        $index = file_get_contents($root.'/index.php');
        echo 'ROOT_ENTRYPOINT_SHA256 '.hash('sha256', $index)."\n";
        // Only bootstrap-related source lines, never configuration/secrets.
        foreach (explode("\n", $index) as $line) {
            if (preg_match('#^\s*(require|\$app\s*=\s*require|if \(file_exists).*?(vendor/autoload|bootstrap/app|maintenance\.php)#', $line)) echo 'BOOTSTRAP_LINE '.trim($line)."\n";
        }
    }
    $phase = 'background-processes';
    $processes = $run(['/bin/ps', '-u', (string) posix_geteuid(), '-o', 'args=']);
    $background = preg_match('/artisan\s+(queue:(work|listen)|horizon|schedule:(run|work))\b/', $processes) === 1;
    echo 'BACKGROUND_ARTISAN_ACTIVE '.($background ? 'yes' : 'no')."\n";
    if ($background) throw new RuntimeException('Background process needs quiescing');
    $phase = 'http-smoke';
    $env = Dotenv\Dotenv::parse(file_get_contents($stage.'/.env'));
    if (($env['DB_DATABASE'] ?? '') !== 'dropoqux_jwtc_stage' || ($env['MAIL_MAILER'] ?? '') !== 'array') throw new RuntimeException('Unsafe staging environment');
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $server = new Symfony\Component\Process\Process([PHP_BINARY, '-S', $address, '-t', $stage.'/public', $stage.'/public/index.php'], $stage, ['APP_ENV' => 'staging', 'APP_DEBUG' => 'false', 'MAIL_MAILER' => 'array', 'QUEUE_CONNECTION' => 'null']);
    $server->setTimeout(90);
    $server->disableOutput();
    $server->start();
    usleep(700000);
    foreach (['/' => 200, '/login' => 200, '/register' => 200, '/contact' => 200, '/admin/dashboard' => 302] as $path => $expected) {
        $status = trim($run(['/usr/bin/curl', '--silent', '--show-error', '--max-time', '20', '--output', '/dev/null', '--write-out', '%{http_code}', '--header', 'Host: joingallagher.uk', 'http://'.$address.$path]));
        echo 'STAGING_HTTP '.$path.' '.$status."\n";
        if ((int) $status !== $expected) throw new RuntimeException('HTTP smoke failed');
    }
    $server->stop(1);
    $server = null;
    $phase = 'dependency-audit';
    $audit = new Symfony\Component\Process\Process([PHP_BINARY, $home.'/bin/composer', 'audit', '--locked', '--no-dev', '--format=json'], $stage);
    $audit->setTimeout(120);
    $audit->run();
    $data = json_decode($audit->getOutput(), true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_key_exists('advisories', $data)) throw new RuntimeException('Dependency audit unavailable');
    $counts = [];
    foreach ($data['advisories'] as $package => $items) {
        foreach ($items as $item) {
            $severity = $item['severity'] ?? 'unknown';
            $counts[$severity] = ($counts[$severity] ?? 0) + 1;
            echo 'ADVISORY '.json_encode(['package' => $package, 'id' => $item['advisoryId'] ?? '', 'severity' => $severity])."\n";
        }
    }
    echo 'DEPENDENCY_AUDIT '.json_encode($counts)."\n";
    if (!empty($counts['critical']) || !empty($counts['high']) || !empty($counts['unknown'])) throw new RuntimeException('Dependency advisories require review');
    file_put_contents($base.'/predeploy-gate.json', json_encode(['passed_at_utc' => gmdate('c'), 'document_root' => $roots[0], 'candidate_commit' => $report['candidate_commit'], 'http_smoke' => true, 'audit' => $counts], JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    echo "PASS: predeployment HTTP and platform gates\n";
} catch (Throwable $e) {
    if (in_array($e->getMessage(), ['UAPI unavailable', 'Unsupported web root', 'Gate command failed'], true)) fwrite(STDERR, 'REASON: '.$e->getMessage()."\n");
    if ($server) $server->stop(1);
    fwrite(STDERR, 'FAIL: predeployment gate at '.$phase."; private details suppressed; production unchanged\n");
    exit(1);
}
