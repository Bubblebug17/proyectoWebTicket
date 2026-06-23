<?php
/**
 * Seed de tickets y mensajes — alineado con la estructura real de la BD.
 *
 * Uso:
 *   php database/seed_tickets_mensajes.php
 *   php database/seed_tickets_mensajes.php --fresh   (vacía tickets y mensajes antes de insertar)
 *   php database/seed_tickets_mensajes.php --count=50
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Database;

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---------------------------------------------------------------------------
// CLI
// ---------------------------------------------------------------------------
$options = getopt('', ['fresh', 'count::']);
$fresh = isset($options['fresh']);
$count = isset($options['count']) ? max(1, (int) $options['count']) : 80;

echo "=== Seed tickets + mensajes ===\n";
echo "Modo: " . ($fresh ? 'FRESH (truncate)' : 'APPEND') . "\n";
echo "Cantidad de tickets: {$count}\n\n";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** @return list<string> Valores permitidos de un campo ENUM según DESCRIBE */
function getEnumValues(\PDO $db, string $table, string $column): array
{
    $stmt = $db->query("DESCRIBE `$table`");
    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        if ($row['Field'] !== $column) {
            continue;
        }
        if (!preg_match("/^enum\('(.+)'\)$/i", $row['Type'], $matches)) {
            throw new RuntimeException("La columna {$table}.{$column} no es ENUM.");
        }
        return explode("','", $matches[1]);
    }
    throw new RuntimeException("Columna {$table}.{$column} no encontrada.");
}

function randomDateBetween(string $from, string $to): string
{
    $ts = mt_rand(strtotime($from), strtotime($to));
    return date('Y-m-d H:i:s', $ts);
}

function slugify(string $text): string
{
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $base = $ascii !== false ? $ascii : $text;
    return strtolower(preg_replace('/[^a-z0-9]/', '', $base));
}

function estadoWeights(array $estados): array
{
    $map = ['abierto' => 45, 'en_proceso' => 35, 'cerrado' => 20];
    return array_map(static fn(string $e): int => $map[$e] ?? 10, $estados);
}

function pickWeighted(array $items, array $weights): mixed
{
    $total = array_sum($weights);
    $rand = mt_rand(1, $total);
    $cumulative = 0;
    foreach ($items as $i => $item) {
        $cumulative += $weights[$i];
        if ($rand <= $cumulative) {
            return $item;
        }
    }
    return $items[array_key_last($items)];
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------
try {
    $db = Database::getConnection();
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Leer estructura real desde la BD (puede diferir de schema.sql versionado)
    $ispValues = getEnumValues($db, 'tickets', 'isp');
    $estadoValues = getEnumValues($db, 'tickets', 'estado');

    echo "ENUM isp detectado:     " . implode(', ', $ispValues) . "\n";
    echo "ENUM estado detectado:  " . implode(', ', $estadoValues) . "\n\n";

    $soporteStmt = $db->query("SELECT id, nombre FROM usuarios WHERE tipo = 'soporte' ORDER BY id ASC");
    $agentes = $soporteStmt->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($agentes)) {
        throw new RuntimeException(
            'No hay usuarios de soporte. Ejecute database/schema.sql o cree al menos un agente.'
        );
    }

    echo 'Agentes de soporte: ' . count($agentes) . "\n";

    if ($fresh) {
        echo "Truncando mensajes y tickets...\n";
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $db->exec('TRUNCATE TABLE mensajes');
        $db->exec('TRUNCATE TABLE tickets');
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Sofía', 'Pedro', 'Lucía', 'Miguel', 'Elena', 'Jorge', 'Laura', 'Ricardo', 'Carmen', 'David', 'Patricia', 'Fernando', 'Gabriela', 'Roberto', 'Valeria'];
    $apellidos = ['Pérez', 'García', 'López', 'Rodríguez', 'Martínez', 'Sánchez', 'Fernández', 'Gómez', 'Díaz', 'Torres', 'Ramírez', 'Vargas', 'Rojas', 'Mendoza', 'Castillo', 'Silva', 'Quispe', 'Huamán', 'Flores', 'Chávez'];

    $problemas = [
        'Internet lento en horas pico',
        'Sin conexión desde ayer',
        'Intermitencia constante del servicio',
        'Router reiniciándose solo',
        'Velocidad inferior al plan contratado',
        'No puedo acceder a portales del ISP',
        'Corte de fibra en el sector',
        'Solicitud de cambio de plan',
        'Duda sobre facturación del mes',
        'Instalación pendiente de verificación',
        'WiFi con alcance muy limitado',
        'Latencia alta en videollamadas',
    ];

    $plantillasDescripcion = [
        'El cliente reporta %s con el proveedor. El inconveniente comenzó hace %d días y afecta el trabajo remoto y servicios del hogar.',
        'Se presenta %s de forma recurrente. Ya se reinició el equipo ONT/router sin mejoría. Requiere revisión técnica a domicilio.',
        'Usuario indica %s. Adjuntó evidencia del problema. Solicita seguimiento prioritario por impacto en su operación diaria.',
    ];

    $respuestasSoporte = [
        'Hemos recibido su reporte y lo estamos revisando. En breve un técnico validará la señal en su zona.',
        'Realizamos pruebas remotas en su enlace. Detectamos variaciones en la potencia óptica; estamos coordinando visita.',
        'Se escaló el caso al área de red. Le mantendremos informado sobre el avance en las próximas horas.',
        'El ticket fue cerrado tras confirmar la restablecida del servicio. Quedamos atentos si el problema se repite.',
        'Por favor confirme si el servicio se normalizó después del último reinicio programado del equipo.',
    ];

    $insertTicket = $db->prepare("
        INSERT INTO tickets (
            nombre, apellido, correo, celular, isp, problema, descripcion,
            archivo, estado, soporte_id, fecha_creacion, fecha_actualizacion
        ) VALUES (
            :nombre, :apellido, :correo, :celular, :isp, :problema, :descripcion,
            :archivo, :estado, :soporte_id, :fecha_creacion, :fecha_actualizacion
        )
    ");

    $insertMensaje = $db->prepare("
        INSERT INTO mensajes (ticket_id, usuario_id, mensaje, fecha_creacion)
        VALUES (:ticket_id, :usuario_id, :mensaje, :fecha_creacion)
    ");

    $db->beginTransaction();

    $insertados = 0;
    $mensajesInsertados = 0;
    $ticketIds = [];

    // Distribución realista: más abiertos, menos cerrados
    $estadoPool = [];
    foreach ($estadoValues as $estado) {
        $estadoPool[] = $estado;
    }

    for ($i = 0; $i < $count; $i++) {
        $nombre = $nombres[array_rand($nombres)];
        $apellido = $apellidos[array_rand($apellidos)];
        $isp = $ispValues[array_rand($ispValues)];
        $problema = $problemas[array_rand($problemas)];
        $estado = pickWeighted($estadoPool, estadoWeights($estadoPool));

        $dias = mt_rand(1, 60);
        $fechaCreacion = randomDateBetween("-{$dias} days", '-1 hour');
        $fechaActualizacion = null;
        $soporteId = null;
        $agente = $agentes[array_rand($agentes)];

        if ($estado !== 'abierto') {
            $soporteId = (int) $agente['id'];
            $fechaActualizacion = randomDateBetween($fechaCreacion, 'now');
        }

        $plantilla = $plantillasDescripcion[array_rand($plantillasDescripcion)];
        $descripcion = sprintf($plantilla, strtolower($problema), mt_rand(1, 14));

        $insertTicket->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => slugify($nombre) . '.' . slugify($apellido) . mt_rand(1, 999) . '@example.com',
            'celular' => '9' . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT),
            'isp' => $isp,
            'problema' => $problema,
            'descripcion' => $descripcion,
            'archivo' => null,
            'estado' => $estado,
            'soporte_id' => $soporteId,
            'fecha_creacion' => $fechaCreacion,
            'fecha_actualizacion' => $fechaActualizacion,
        ]);

        $ticketId = (int) $db->lastInsertId();
        $ticketIds[] = $ticketId;
        $insertados++;

        // Nota de seguimiento interno (~40% de tickets)
        if (mt_rand(1, 100) <= 40) {
            $insertMensaje->execute([
                'ticket_id' => $ticketId,
                'usuario_id' => (int) $agente['id'],
                'mensaje' => 'Ticket registrado en cola de atención. Prioridad asignada según tipo de incidencia reportada.',
                'fecha_creacion' => date('Y-m-d H:i:s', strtotime($fechaCreacion . ' +15 minutes')),
            ]);
            $mensajesInsertados++;
        }

        // Hilos de soporte según estado
        if ($estado === 'en_proceso' || $estado === 'cerrado') {
            $numRespuestas = $estado === 'cerrado' ? mt_rand(2, 4) : mt_rand(1, 3);
            $baseTs = strtotime($fechaCreacion);

            for ($m = 0; $m < $numRespuestas; $m++) {
                $insertMensaje->execute([
                    'ticket_id' => $ticketId,
                    'usuario_id' => (int) $agente['id'],
                    'mensaje' => $respuestasSoporte[array_rand($respuestasSoporte)],
                    'fecha_creacion' => date('Y-m-d H:i:s', $baseTs + (($m + 1) * mt_rand(3600, 86400))),
                ]);
                $mensajesInsertados++;
            }
        }
    }

    $db->commit();

    echo "\n--- Resultado ---\n";
    echo "Tickets insertados:  {$insertados}\n";
    echo "Mensajes insertados: {$mensajesInsertados}\n";

    $resumen = $db->query("
        SELECT estado, COUNT(*) AS total
        FROM tickets
        GROUP BY estado
        ORDER BY FIELD(estado, 'abierto', 'en_proceso', 'cerrado')
    ")->fetchAll(\PDO::FETCH_ASSOC);

    echo "\nDistribución por estado:\n";
    foreach ($resumen as $row) {
        echo "  - {$row['estado']}: {$row['total']}\n";
    }

    $ispResumen = $db->query("
        SELECT isp, COUNT(*) AS total FROM tickets GROUP BY isp
    ")->fetchAll(\PDO::FETCH_ASSOC);

    echo "\nDistribución por ISP:\n";
    foreach ($ispResumen as $row) {
        echo "  - {$row['isp']}: {$row['total']}\n";
    }

    echo "\nSeed completado correctamente.\n";

} catch (\PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Error de base de datos: " . $e->getMessage() . "\n");
    exit(1);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
