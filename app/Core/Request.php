<?php

namespace App\Core;

/**
 * Clase para manejar las peticiones HTTP
 */
class Request
{
    /**
     * Obtiene el método HTTP
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Obtiene la ruta de la petición
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        // Remover el base path
        $basePath = '/proyectoWebTicket';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        return $path === '' ? '/' : rtrim($path, '/');
    }

    /**
     * Obtiene un parámetro GET
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtiene un parámetro POST
     */
    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtiene todos los parámetros POST
     */
    public function all(): array
    {
        return $_POST;
    }

    /**
     * Verifica si la petición es POST
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Verifica si la petición es GET
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }
}
