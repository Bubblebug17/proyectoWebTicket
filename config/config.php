<?php
/**
 * Configuración principal de la aplicación
 */

return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'ticket_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Sistema de Tickets',
        'url' => 'http://localhost/proyectoWebTicket',
        'timezone' => 'America/Lima'
    ],
    'session' => [
        'lifetime' => 3600, // 1 hora
        'name' => 'TICKET_SESSION'
    ]
];
