<?php
/**
 * Descarga PHPUnit como .phar (sin depender de vendor/ para testing).
 *
 * Uso: php tools/install-phpunit.php
 */

declare(strict_types=1);

$target = __DIR__ . '/phpunit.phar';

$sources = [
    'https://phar.phpunit.de/phpunit-11.phar',
    'https://github.com/sebastianbergmann/phpunit/releases/download/11.5.55/phpunit-11.5.phar',
];

$context = stream_context_create([
    'http' => [
        'follow_location' => 1,
        'timeout' => 120,
        'user_agent' => 'proyectoWebTicket-install-phpunit',
    ],
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ],
]);

foreach ($sources as $url) {
    echo "Intentando descargar desde: {$url}\n";
    $phar = @file_get_contents($url, false, $context);

    if ($phar !== false && file_put_contents($target, $phar) !== false) {
        $mb = round(strlen($phar) / 1024 / 1024, 2);
        echo "PHPUnit instalado en: {$target} ({$mb} MB)\n";
        echo "Ejecutar pruebas: php tools/phpunit.phar --testsuite Unit\n";
        exit(0);
    }
}

fwrite(STDERR, "Error: no se pudo descargar PHPUnit desde ninguna fuente.\n");
exit(1);
