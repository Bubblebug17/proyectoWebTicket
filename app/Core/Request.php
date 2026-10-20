<?php

namespace App\Core;

/**
 * Clase para manejar las peticiones HTTP con sanitización básica de entradas.
 */
class Request
{
    /**
     * Obtiene el método HTTP
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Obtiene la ruta de la petición
     */
    public function getPath(): string
    {
        $path = $_SERVER['HTTP_X_ORIGINAL_URL']
            ?? $_SERVER['UNENCODED_URL']
            ?? $_SERVER['REQUEST_URI']
            ?? '/';
        $path = str_replace('\\', '/', (string) $path);
        $position = strpos($path, '?');

        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        $basePath = AppUrl::basePath();
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        if ($path === '/index.php' || str_ends_with($path, '/index.php')) {
            $path = '/';
        }

        return $path === '' ? '/' : rtrim($path, '/');
    }

    /**
     * Obtiene un parámetro GET sanitizado
     */
    public function get(string $key, $default = null)
    {
        if (!isset($_GET[$key])) {
            return $default;
        }

        return $this->sanitizeValue($_GET[$key]);
    }

    /**
     * Obtiene un parámetro POST sanitizado
     */
    public function post(string $key, $default = null)
    {
        if (!isset($_POST[$key])) {
            return $default;
        }

        return $this->sanitizeValue($_POST[$key]);
    }

    /**
     * Obtiene todos los parámetros POST sanitizados
     */
    public function all(): array
    {
        $sanitized = [];
        foreach ($_POST as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value);
        }

        return $sanitized;
    }

    /**
     * Limpia strings de etiquetas HTML, entidades peligrosas y caracteres de control.
     */
    private function sanitizeValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $clean = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $clean) ?? $clean;
        $clean = strip_tags($clean);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean) ?? $clean;
        $clean = str_replace(["\0", "\r"], '', $clean);

        return trim($clean);
    }
}
