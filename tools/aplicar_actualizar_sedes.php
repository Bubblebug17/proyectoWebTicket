<?php
/**
 * Aplica database/actualizar_usuarios_sedes.sql en la base de config/config.php.
 *
 * Uso: php tools/aplicar_actualizar_sedes.php
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

error_reporting(E_ALL);
ini_set('display_errors', '1');

$config = require CONFIG_PATH . '/config.php';
$db = $config['database'];

$host = (string) $db['host'];
$port = isset($db['port']) ? (int) $db['port'] : 0;
if ($port < 1 && preg_match('/^(.+):(\d+)$/', $host, $hostPort) === 1) {
    $host = $hostPort[1];
    $port = (int) $hostPort[2];
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $host,
    $db['dbname'],
    $db['charset'] ?? 'utf8mb4'
);
if ($port > 0) {
    $dsn .= ';port=' . $port;
}

$sqlPath = ROOT_PATH . '/database/actualizar_usuarios_sedes.sql';
$sql = file_get_contents($sqlPath);
if ($sql === false) {
    fwrite(STDERR, "No se encontró {$sqlPath}\n");
    exit(1);
}

$sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
$sentencias = array_filter(array_map('trim', explode(';', $sql)), static fn(string $s): bool => $s !== '');

try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Conexión fallida: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "Aplicando actualización de usuarios por sede...\n";

foreach ($sentencias as $sentencia) {
    $resumen = substr(preg_replace('/\s+/', ' ', $sentencia) ?? $sentencia, 0, 80);
    $filas = $pdo->exec($sentencia);
    echo "OK ({$filas} filas): {$resumen}\n";
}

$stmt = $pdo->query("
    SELECT u.email, u.tipo, s.codigo AS sede, t.codigo AS area
    FROM usuarios u
    INNER JOIN sucursales s ON s.id = u.sucursal_id
    LEFT JOIN tipos_ticket t ON t.id = u.area_id
    ORDER BY u.id
");
echo "\nUsuarios tras la actualización:\n";
foreach ($stmt as $row) {
    echo sprintf(
        "  %s | %s | %s | area=%s\n",
        $row['email'],
        $row['tipo'],
        $row['sede'],
        $row['area'] ?? '-'
    );
}

echo "\nListo.\n";
