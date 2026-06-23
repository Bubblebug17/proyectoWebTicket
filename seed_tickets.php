<?php
/**
 * Punto de entrada legacy — delega al seed actualizado.
 *
 * @deprecated Usar: php database/seed_tickets_mensajes.php [--fresh] [--count=N]
 */
passthru('"' . PHP_BINARY . '" "' . __DIR__ . '/database/seed_tickets_mensajes.php"' . implode('', array_map(
    static fn(string $arg): string => ' ' . escapeshellarg($arg),
    array_slice($argv, 1)
)), $exitCode);

exit($exitCode);
