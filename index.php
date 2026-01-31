<?php
/**
 * Punto de entrada principal de la aplicación
 */

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar configuración
require_once CONFIG_PATH . '/config.php';

// Iniciar la aplicación
$app = new App\Core\Application();
$app->run();
