<?php
/**
 * Prepara la base configurada en config/config.php.
 * No crea otra base ni borra tickets. Se puede ejecutar más de una vez.
 *
 * Uso: php tools/montar.php
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

echo "Conectando a {$host}" . ($port > 0 ? ":{$port}" : '') . " / {$db['dbname']}...\n";

try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 15,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo conectar a la base remota: " . $e->getMessage() . "\n");
    exit(1);
}

$sql = file_get_contents(ROOT_PATH . '/database/schema.sql');
if ($sql === false) {
    fwrite(STDERR, "No se encontró database/schema.sql\n");
    exit(1);
}

$sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
$sentencias = array_filter(array_map('trim', explode(';', $sql)), static fn(string $s): bool => $s !== '');

$aplicadas = 0;
$omitidas = 0;

foreach ($sentencias as $sentencia) {
    if (preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $sentencia) === 1) {
        $omitidas++;
        continue;
    }

    if (preg_match('/^ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+CONSTRAINT\s+`?(\w+)`?/i', $sentencia, $restriccion) === 1) {
        $existe = $pdo->prepare("
            SELECT 1
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :tabla
              AND CONSTRAINT_NAME = :nombre
        ");
        $existe->execute([
            'tabla' => $restriccion[1],
            'nombre' => $restriccion[2],
        ]);
        if ($existe->fetchColumn()) {
            echo "Ya estaba: restricción {$restriccion[2]}\n";
            $omitidas++;
            continue;
        }
    }

    $resumen = preg_replace('/\s+/', ' ', $sentencia) ?? $sentencia;
    $resumen = substr($resumen, 0, 90);

    try {
        $pdo->exec($sentencia);
        echo "OK  {$resumen}\n";
        $aplicadas++;
    } catch (PDOException $e) {
        $codigo = (int) ($e->errorInfo[1] ?? 0);
        // Duplicados al volver a ejecutar: clave, índice, restricción o fila.
        if (in_array($codigo, [1022, 1050, 1060, 1061, 1062, 1826], true)) {
            echo "Ya estaba: {$resumen}\n";
            $omitidas++;
            continue;
        }

        fwrite(STDERR, "Error SQL ({$codigo}) en: {$resumen}\n" . $e->getMessage() . "\n");
        exit(1);
    }
}

$conteos = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM sucursales) AS sucursales,
        (SELECT COUNT(*) FROM usuarios) AS usuarios,
        (SELECT COUNT(*) FROM tipos_ticket) AS tipos,
        (SELECT COUNT(*) FROM subtipos_ticket) AS subtipos,
        (SELECT COUNT(*) FROM tickets) AS tickets
")->fetch();

echo "\nBase lista.\n";
echo "Sentencias aplicadas: {$aplicadas}. Omitidas: {$omitidas}.\n";
echo "Sucursales: {$conteos['sucursales']}. Usuarios: {$conteos['usuarios']}. ";
echo "Tipos: {$conteos['tipos']}. Motivos: {$conteos['subtipos']}. Tickets: {$conteos['tickets']}.\n";
