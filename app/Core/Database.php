<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Clase para manejar la conexión a la base de datos
 */
class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];

    /**
     * Obtiene la conexión a la base de datos
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Establece la conexión a la base de datos
     */
    private static function connect(): void
    {
        if (empty(self::$config)) {
            $configFile = CONFIG_PATH . '/config.php';
            $config = require $configFile;
            self::$config = $config['database'];
        }

        try {
            $host = (string) self::$config['host'];
            $port = isset(self::$config['port']) ? (int) self::$config['port'] : 0;
            if ($port < 1 && preg_match('/^(.+):(\d+)$/', $host, $hostPort) === 1) {
                $host = $hostPort[1];
                $port = (int) $hostPort[2];
            }

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $host,
                self::$config['dbname'],
                self::$config['charset']
            );
            if ($port > 0) {
                $dsn .= ';port=' . $port;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            self::$connection = new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                $options
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
