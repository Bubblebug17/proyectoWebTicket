<?php

return [
    'database' => [
        'host' => '162.0.228.203:3306',
        'dbname' => 'creantis_efsrt_proy',
        'username' => 'creantis_efsrt_proy',
        'password' => 't#a7+f3dPJ{d',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Intranet Tambo',
        // null o 'auto': se deduce del host y SCRIPT_NAME (IIS, Apache, subcarpeta o raíz).
        'url' => null,
        // null: prefijo automático; '' raíz del sitio; '/mi-carpeta' para forzar.
        'base_path' => null,
        'timezone' => 'America/Lima',
    ],
    'session' => [
        'lifetime' => 3600, // 1 hora
        'name' => 'TICKET_SESSION'
    ]
];
