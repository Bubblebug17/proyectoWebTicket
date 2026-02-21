<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use App\Core\Database;

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CONFIG_PATH', __DIR__ . '/config');

echo "Iniciando población de datos...\n";

try {
    $db = Database::getConnection();

    // Arrays de datos aleatorios
    $nombres = ['Juan', 'Maria', 'Carlos', 'Ana', 'Luis', 'Sofia', 'Pedro', 'Lucia', 'Miguel', 'Elena', 'Jorge', 'Laura', 'Ricardo', 'Carmen', 'David'];
    $apellidos = ['Perez', 'Garcia', 'Lopez', 'Rodriguez', 'Martinez', 'Sanchez', 'Fernandez', 'Gomez', 'Diaz', 'Torres', 'Ramirez', 'Vargas', 'Rojas', 'Mendoza', 'Castillo'];
    $isps = ['Claro', 'Win', 'Movistar', 'Bitel', 'Fibratel'];
    $problemas = [
        'Internet lento',
        'Sin conexión',
        'Intermitencia en el servicio',
        'Problemas con el router',
        'Velocidad baja en horas pico',
        'No puedo acceder a ciertas páginas',
        'Cable de fibra roto',
        'Solicitud de cambio de plan',
        'Problemas de facturación',
        'Soporte técnico general'
    ];
    $estados = ['abierto', 'en_proceso', 'cerrado'];

    $stmt = $db->prepare("
        INSERT INTO tickets (nombre, apellido, correo, celular, isp, problema, descripcion, estado, fecha_creacion)
        VALUES (:nombre, :apellido, :correo, :celular, :isp, :problema, :descripcion, :estado, :fecha_creacion)
    ");

    for ($i = 0; $i < 100; $i++) {
        $nombre = $nombres[array_rand($nombres)];
        $apellido = $apellidos[array_rand($apellidos)];
        $isp = $isps[array_rand($isps)];
        $problema = $problemas[array_rand($problemas)];
        $estado = $estados[array_rand($estados)];
        
        // Generar fecha aleatoria en los últimos 30 días
        $timestamp = mt_rand(strtotime('-30 days'), time());
        $fecha = date('Y-m-d H:i:s', $timestamp);

        $params = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => strtolower($nombre . '.' . $apellido . '@example.com'),
            'celular' => '9' . mt_rand(10000000, 99999999),
            'isp' => $isp,
            'problema' => $problema,
            'descripcion' => "Descripción detallada del problema: $problema. Experimentado por el cliente $nombre $apellido con el proveedor $isp.",
            'estado' => $estado,
            'fecha_creacion' => $fecha
        ];

        $stmt->execute($params);
    }

    echo "¡Se han insertado 100 tickets exitosamente!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
