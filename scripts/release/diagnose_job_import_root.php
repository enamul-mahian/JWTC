<?php
// No writes: diagnose cPanel document-root metadata used by the release backup.
ini_set('display_errors', '0');
try {
    $root = realpath('.');
    $home = realpath(getenv('HOME') ?: '');
    if (!$root || !$home || $root !== $home.'/joingallagher.uk') throw new RuntimeException('Wrong account or root');
    $uapi = '/usr/bin/uapi';
    if (!is_executable($uapi)) $uapi = '/usr/local/bin/uapi';
    if (!is_executable($uapi)) throw new RuntimeException('uapi unavailable');
    $run = static function(array $args): array {
        $p = new Symfony\Component\Process\Process($args);
        $p->setTimeout(30);
        $p->run();
        if (!$p->isSuccessful()) throw new RuntimeException('UAPI query failed');
        $data = json_decode($p->getOutput(), true, 64, JSON_THROW_ON_ERROR);
        if (($data['result']['status'] ?? 0) !== 1) throw new RuntimeException('UAPI status rejected');
        return $data['result']['data'] ?? [];
    };
    require $root.'/vendor/autoload.php';
    $data = $run([$uapi, '--output=json', 'DomainInfo', 'single_domain_data', 'domain=joingallagher.uk']);
    $reported = [];
    $walk = static function($node) use (&$walk, &$reported, $root): void {
        if (!is_array($node)) return;
        foreach ($node as $key => $val) {
            if (in_array($key,['documentroot','document_root'],true) && is_string($val)) {
                $r = realpath($val);
                $reported[] = [
                    'exists' => $r !== false,
                    'same_app_root' => $r === $root,
                    'inside_app_root' => $r !== false && str_starts_with($r, $root.'/'),
                    'app_root_inside_document_root' => $r !== false && str_starts_with($root, $r.'/'),
                    'path_fingerprint' => substr(hash('sha256',$val),0,12),
                ];
            } elseif (is_array($val)) $walk($val);
        }
    };
    $walk($data);
    echo "DOCUMENT_ROOT_DIAGNOSTIC ".json_encode($reported,JSON_THROW_ON_ERROR)."\n";
    $all = $run([$uapi, '--output=json', 'DomainInfo', 'domains_data', 'format=list']);
    $allReport = [];
    $walk($all);
    echo "ALL_DOMAIN_ROOTS_DIAGNOSTIC ".json_encode(array_slice($reported,1),JSON_THROW_ON_ERROR)."\n";
    echo "APP_ROOT_VERIFIED yes\n";
} catch (Throwable $e) {
    echo "DIAGNOSTIC_STOP ".get_class($e)."\n";
    exit(1);
}
