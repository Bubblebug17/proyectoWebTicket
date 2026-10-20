<?php
/**
 * Genera tickets de sucursales Tambo con mensajes de seguimiento.
 *
 * Uso:
 *   php database/seed_tickets_mensajes.php
 *   php database/seed_tickets_mensajes.php --fresh   (vacía tickets y mensajes antes de insertar)
 *   php database/seed_tickets_mensajes.php --count=80
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Database;

error_reporting(E_ALL);
ini_set('display_errors', '1');

$options = getopt('', ['fresh', 'count::']);
$fresh = isset($options['fresh']);
$count = isset($options['count']) ? max(1, (int) $options['count']) : 80;

echo "=== Seed tickets Tambo ===\n";
echo "Modo: " . ($fresh ? 'FRESH (truncate)' : 'APPEND') . "\n";
echo "Cantidad de tickets: {$count}\n\n";

function randomDateBetween(string $from, string $to): string
{
    $inicio = strtotime($from);
    $fin = strtotime($to);
    if ($fin < $inicio) {
        $fin = $inicio;
    }
    return date('Y-m-d H:i:s', mt_rand($inicio, $fin));
}

/** @param list<int> $weights */
function pickWeighted(array $items, array $weights): mixed
{
    $total = array_sum($weights);
    $rand = mt_rand(1, max(1, $total));
    $cumulative = 0;
    foreach ($items as $i => $item) {
        $cumulative += $weights[$i];
        if ($rand <= $cumulative) {
            return $item;
        }
    }
    return $items[array_key_last($items)];
}

try {
    $db = Database::getConnection();
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $sucursales = $db->query("
        SELECT id, codigo, nombre
        FROM sucursales
        ORDER BY orden, id
    ")->fetchAll(\PDO::FETCH_ASSOC);

    $subtipos = $db->query("
        SELECT s.id, s.codigo, s.nombre, s.tipo_id, t.codigo AS tipo_codigo, t.nombre AS tipo_nombre
        FROM subtipos_ticket s
        INNER JOIN tipos_ticket t ON t.id = s.tipo_id
        ORDER BY t.orden, s.orden
    ")->fetchAll(\PDO::FETCH_ASSOC);

    $agentes = $db->query("
        SELECT id, nombre, apellido
        FROM usuarios
        WHERE tipo = 'soporte'
        ORDER BY id
    ")->fetchAll(\PDO::FETCH_ASSOC);

    $encargados = $db->query("
        SELECT id, area_id, nombre, apellido
        FROM usuarios
        WHERE tipo = 'responsable_area' AND area_id IS NOT NULL
    ")->fetchAll(\PDO::FETCH_ASSOC);

    $tiendas = $db->query("
        SELECT u.nombre, u.apellido, u.email, u.celular, u.sucursal_id
        FROM usuarios u
        WHERE u.tipo = 'responsable_tienda'
        ORDER BY u.id
    ")->fetchAll(\PDO::FETCH_ASSOC);

    if ($sucursales === [] || $subtipos === [] || $agentes === [] || $encargados === []) {
        throw new RuntimeException(
            'Faltan sucursales, catálogo, soporte o encargados de área. Ejecute las migraciones antes del seed.'
        );
    }

    $encargadoPorArea = [];
    foreach ($encargados as $encargado) {
        $encargadoPorArea[(int) $encargado['area_id']] = $encargado;
    }

    $tiendaPorSucursal = [];
    foreach ($tiendas as $tienda) {
        $tiendaPorSucursal[(int) $tienda['sucursal_id']] = $tienda;
    }

    $responsablesExtra = [
        'TB-SBO' => ['nombre' => 'Diego', 'apellido' => 'Salazar', 'email' => 'diego.salazar@tambo.pe', 'celular' => '987120334'],
        'TB-OLI' => ['nombre' => 'Carmen', 'apellido' => 'Villegas', 'email' => 'carmen.villegas@tambo.pe', 'celular' => '987441902'],
        'TB-MIR' => ['nombre' => 'María', 'apellido' => 'Quispe', 'email' => 'tienda@demo.com', 'celular' => '999111222'],
    ];

    $porTipo = [];
    foreach ($subtipos as $subtipo) {
        $porTipo[(int) $subtipo['tipo_id']][] = $subtipo;
    }
    $tipoIds = array_keys($porTipo);

    $detalles = [
        'falta_stock_lider' => [
            'No queda stock de productos líder en góndola. El último pedido no cubrió la venta del fin de semana.',
            'La tienda se quedó sin los SKU líder de bebidas y snacks. Se necesita reposición antes del turno noche.',
        ],
        'lote_vencido' => [
            'Se detectó un lote vencido en el lineal de abarrotes. Hay que retirarlo y reponer la mercadería.',
            'El control de vencimientos encontró productos fuera de fecha en la cámara. Se apartó el lote.',
        ],
        'falta_uso_diario' => [
            'Faltan bolsas, vasos y tapers para la operación del día. El consumo superó el stock de tienda.',
            'No hay suficientes elementos de uso diario para el despacho de mostrador.',
        ],
        'insumos_sanitarios' => [
            'Se agotaron jabón, papel y alcohol del baño de personal y de la zona de atención.',
            'Hay que reabastecer los insumos sanitarios antes de la apertura de mañana.',
        ],
        'pedido_no_recepcionado' => [
            'El pedido del centro de distribución no llegó en la ventana de recepción. La guía figura en tránsito.',
            'La tienda no recepcionó el pedido programado. Faltan cajas del despacho de hoy.',
        ],
        'diferencia_inventario' => [
            'El conteo de tienda no coincide con el sistema en varias categorías. Hay faltantes sin justificar.',
            'Se encontró diferencia de inventario al cerrar el día. Se pide revisión del movimiento.',
        ],
        'falla_pos' => [
            'El punto de venta se congela al cobrar. La caja no puede emitir el comprobante.',
            'El POS reinicia solo durante el pago con tarjeta y deja la venta a medias.',
        ],
        'impresora_tickets' => [
            'La impresora de tickets no corta el papel y sale el comprobante en blanco.',
            'No imprime vouchers desde media mañana. Se está anotando la venta de forma manual.',
        ],
        'sin_conexion' => [
            'La tienda se quedó sin internet y no puede consultar precios ni sincronizar ventas.',
            'La red cae por intervalos. El datáfono y el inventario pierden conexión.',
        ],
        'sistema_inventario' => [
            'El sistema de inventario no carga el stock de tienda. No se pueden confirmar recepciones.',
            'La consulta de kardex devuelve error y retrasa el cuadre de mercadería.',
        ],
        'lector_barras' => [
            'El lector de código de barras no reconoce varios SKU. La cajera ingresa los códigos a mano.',
            'El lector pita pero no registra el producto en el POS.',
        ],
        'equipo_frio' => [
            'La refrigeradora de bebidas no mantiene temperatura. Los productos están fuera de rango.',
            'El equipo de frío hace ruido y la temperatura subió por encima de lo permitido.',
        ],
        'error_cierre_caja' => [
            'El cierre de caja no cuadra con los comprobantes del turno. Falta registrar un pago.',
            'Al cerrar caja el sistema muestra un error y no deja confirmar el turno.',
        ],
        'diferencia_arqueo' => [
            'El arqueo del turno tarde tiene diferencia en efectivo. Se apartó el sobre para revisión.',
            'Hay un faltante en el arqueo respecto de las ventas en efectivo del día.',
        ],
        'cambio_turno' => [
            'Se solicita cambio de turno por cobertura de la tienda el fin de semana.',
            'El responsable pide ajustar el turno de apertura porque no hay personal de reemplazo.',
        ],
        'incidencia_personal' => [
            'Un colaborador no se presentó y la tienda opera con dotación incompleta.',
            'Se reporta una incidencia de personal durante el turno noche. Se necesita indicación del área.',
        ],
        'documentacion_incompleta' => [
            'La guía del pedido llegó sin el detalle de bultos. No se puede cerrar la recepción.',
            'Falta la factura de una entrega ya ingresada a tienda. La documentación está incompleta.',
        ],
        'reclamo_cliente' => [
            'Un cliente reclama un cobro duplicado en el POS. Se guardó el voucher para la revisión.',
            'Quedó pendiente el reclamo de un producto en mal estado entregado en mostrador.',
        ],
        'incidente_seguridad' => [
            'Se reporta un intento de hurto en la zona de bebidas. Se resguardó la cámara del pasillo.',
            'Hubo un incidente de seguridad al cierre. Se pide revisión de la cerradura y las cámaras.',
        ],
        'dano_infraestructura' => [
            'Hay una filtración en el techo del almacén. El agua cae cerca de la mercadería seca.',
            'Se dañó una puerta del local y no cierra bien al terminar el turno.',
        ],
        'servicios_basicos' => [
            'Se cortó la luz de la tienda por más de una hora. Los equipos de frío quedaron sin energía.',
            'No hay agua en los servicios de la tienda desde el inicio del turno.',
        ],
        'mantenimiento_local' => [
            'El local necesita limpieza profunda del almacén y revisión de la iluminación del pasillo.',
            'Hay que programar mantenimiento del piso de venta: está levantado junto a la caja.',
        ],
        'consulta_no_clasificada' => [
            'La tienda consulta un caso que no encaja en los motivos habituales y pide orientación.',
            'Se registra una consulta operativa pendiente de clasificación por el área correspondiente.',
        ],
        'sugerencia_mejora' => [
            'La tienda sugiere cambiar el orden de reposición de la góndola de productos líder.',
            'Se propone ajustar el horario de recepción para no cruzarlo con la hora punta de venta.',
        ],
    ];

    $respuestasSoporte = [
        'Recibimos el ticket de la tienda y lo estamos revisando con el área correspondiente.',
        'Quedó registrado el caso. Vamos a confirmar el pedido y el impacto en la operación de hoy.',
        'Coordinamos la atención con el encargado del área. Les avisamos apenas haya una fecha.',
        'Revisamos la evidencia de la tienda. El caso sigue en seguimiento.',
    ];

    $respuestasEncargado = [
        'Tomo el ticket de mi área. Estoy revisando el stock y la última recepción de la tienda.',
        'Ya derivé la reposición al equipo responsable. Confirmen cuando llegue a tienda.',
        'El caso quedó resuelto. Pueden cerrar la incidencia si la operación volvió a la normalidad.',
        'Necesito el detalle del turno y una foto del lineal o del equipo para continuar.',
    ];

    if ($fresh) {
        echo "Truncando mensajes y tickets...\n";
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $db->exec('TRUNCATE TABLE mensajes');
        $db->exec('TRUNCATE TABLE tickets');
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    $insertTicket = $db->prepare("
        INSERT INTO tickets (
            nombre, apellido, correo, celular, sucursal,
            tipo_id, subtipo_id, descripcion, archivo, estado,
            soporte_id, encargado_id, fecha_creacion, fecha_actualizacion
        ) VALUES (
            :nombre, :apellido, :correo, :celular, :sucursal,
            :tipo_id, :subtipo_id, :descripcion, NULL, :estado,
            :soporte_id, :encargado_id, :fecha_creacion, :fecha_actualizacion
        )
    ");

    $insertMensaje = $db->prepare("
        INSERT INTO mensajes (ticket_id, usuario_id, mensaje, fecha_creacion)
        VALUES (:ticket_id, :usuario_id, :mensaje, :fecha_creacion)
    ");

    $db->beginTransaction();

    $insertados = 0;
    $mensajesInsertados = 0;
    $estados = ['abierto', 'en_proceso', 'cerrado'];
    $pesosEstado = [40, 40, 20];

    for ($i = 0; $i < $count; $i++) {
        $tipoId = $tipoIds[$i % count($tipoIds)];
        $subtipo = $porTipo[$tipoId][array_rand($porTipo[$tipoId])];
        $sucursal = $sucursales[array_rand($sucursales)];
        $estado = pickWeighted($estados, $pesosEstado);

        $solicitante = $tiendaPorSucursal[(int) $sucursal['id']]
            ?? $responsablesExtra[$sucursal['codigo']]
            ?? $responsablesExtra['TB-MIR'];

        $dias = mt_rand(1, 40);
        $fechaCreacion = randomDateBetween("-{$dias} days", '-2 hours');
        $soporteId = null;
        $encargadoId = null;
        $fechaActualizacion = null;
        $agente = $agentes[array_rand($agentes)];
        $encargado = $encargadoPorArea[(int) $subtipo['tipo_id']] ?? null;

        if ($estado !== 'abierto') {
            $soporteId = (int) $agente['id'];
            $fechaActualizacion = randomDateBetween($fechaCreacion, 'now');
            if ($encargado !== null) {
                $encargadoId = (int) $encargado['id'];
            }
        }

        $variantes = $detalles[$subtipo['codigo']] ?? [
            'La tienda reporta: ' . $subtipo['nombre'] . '.',
        ];

        $insertTicket->execute([
            'nombre' => $solicitante['nombre'],
            'apellido' => $solicitante['apellido'],
            'correo' => $solicitante['email'],
            'celular' => $solicitante['celular'],
            'sucursal' => $sucursal['codigo'] . ' · ' . $sucursal['nombre'],
            'tipo_id' => (int) $subtipo['tipo_id'],
            'subtipo_id' => (int) $subtipo['id'],
            'descripcion' => $variantes[array_rand($variantes)],
            'estado' => $estado,
            'soporte_id' => $soporteId,
            'encargado_id' => $encargadoId,
            'fecha_creacion' => $fechaCreacion,
            'fecha_actualizacion' => $fechaActualizacion,
        ]);

        $ticketId = (int) $db->lastInsertId();
        $insertados++;

        if ($estado === 'abierto') {
            continue;
        }

        $base = strtotime($fechaCreacion);
        $ahora = time();
        $numRespuestas = $estado === 'cerrado' ? mt_rand(2, 3) : mt_rand(1, 2);

        for ($m = 0; $m < $numRespuestas; $m++) {
            $cuando = min($ahora, $base + (($m + 1) * mt_rand(1800, 43200)));
            $esEncargado = $encargadoId !== null && $m === $numRespuestas - 1;
            $insertMensaje->execute([
                'ticket_id' => $ticketId,
                'usuario_id' => $esEncargado ? $encargadoId : $soporteId,
                'mensaje' => $esEncargado
                    ? $respuestasEncargado[array_rand($respuestasEncargado)]
                    : $respuestasSoporte[array_rand($respuestasSoporte)],
                'fecha_creacion' => date('Y-m-d H:i:s', $cuando),
            ]);
            $mensajesInsertados++;
        }
    }

    $db->commit();

    echo "Tickets insertados en esta ejecución: {$insertados}\n";
    echo "Mensajes insertados en esta ejecución: {$mensajesInsertados}\n";

    $resumen = $db->query("
        SELECT estado, COUNT(*) AS total
        FROM tickets
        GROUP BY estado
        ORDER BY FIELD(estado, 'abierto', 'en_proceso', 'cerrado')
    ")->fetchAll(\PDO::FETCH_ASSOC);

    echo "\nTickets en la base, por estado:\n";
    foreach ($resumen as $row) {
        echo "  - {$row['estado']}: {$row['total']}\n";
    }

    $porArea = $db->query("
        SELECT t.nombre AS area, COUNT(tk.id) AS total,
               SUM(tk.encargado_id IS NOT NULL) AS derivados
        FROM tipos_ticket t
        LEFT JOIN tickets tk ON tk.tipo_id = t.id
        GROUP BY t.id, t.nombre
        ORDER BY t.orden
    ")->fetchAll(\PDO::FETCH_ASSOC);

    echo "\nPor área:\n";
    foreach ($porArea as $row) {
        echo "  - {$row['area']}: {$row['total']} (derivados: {$row['derivados']})\n";
    }

    echo "\nSeed completado.\n";
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
