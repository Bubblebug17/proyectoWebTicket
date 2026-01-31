<?php

namespace App\Core;

/**
 * Clase para manejar sesiones
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Establece un valor en la sesión
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtiene un valor de la sesión
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica si existe una clave en la sesión
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Elimina un valor de la sesión
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destruye la sesión
     */
    public function destroy(): void
    {
        session_destroy();
    }

    /**
     * Obtiene todos los datos de la sesión
     */
    public function all(): array
    {
        return $_SESSION;
    }
}
