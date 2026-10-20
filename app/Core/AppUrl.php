<?php

namespace App\Core;

/**
 * URL pública y prefijo de ruta detectados o configurados (Apache, IIS, subcarpeta o raíz del sitio).
 */
final class AppUrl
{
    private static bool $initialized = false;

    private static string $basePath = '';

    private static string $baseUrl = '';

    /** @param array<string, mixed> $appConfig */
    public static function init(array $appConfig): void
    {
        if (self::$initialized) {
            return;
        }

        self::$basePath = self::resolveBasePath($appConfig);
        self::$baseUrl = self::resolveBaseUrl($appConfig);
        self::$initialized = true;
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function baseUrl(): string
    {
        return self::$baseUrl;
    }

    /**
     * Ruta absoluta en el navegador (sin query string).
     */
    public static function path(string $route = ''): string
    {
        $route = ltrim(str_replace('\\', '/', $route), '/');

        if ($route === '') {
            return self::$basePath === '' ? '/' : self::$basePath;
        }

        return (self::$basePath === '' ? '' : self::$basePath) . '/' . $route;
    }

    /**
     * Ruta con query string opcional (ej. soporte/tickets/view?id=3).
     */
    public static function to(string $routeWithQuery = ''): string
    {
        $routeWithQuery = ltrim($routeWithQuery, '/');
        if ($routeWithQuery === '') {
            return self::path('');
        }

        $question = strpos($routeWithQuery, '?');
        if ($question === false) {
            return self::path($routeWithQuery);
        }

        $route = substr($routeWithQuery, 0, $question);
        $query = substr($routeWithQuery, $question);

        return self::path($route) . $query;
    }

    /** @param array<string, mixed> $appConfig */
    private static function resolveBasePath(array $appConfig): string
    {
        if (array_key_exists('base_path', $appConfig) && $appConfig['base_path'] !== null) {
            $configured = trim((string) $appConfig['base_path'], '/');

            return $configured === '' ? '' : '/' . $configured;
        }

        $detected = self::detectBasePathFromServer();
        if ($detected !== '') {
            return $detected;
        }

        $fromUrl = self::pathFromConfiguredUrl($appConfig['url'] ?? null);
        if ($fromUrl !== null) {
            return $fromUrl;
        }

        return '';
    }

    /** @param array<string, mixed> $appConfig */
    private static function resolveBaseUrl(array $appConfig): string
    {
        $configured = $appConfig['url'] ?? null;
        if (is_string($configured) && $configured !== '' && strtolower($configured) !== 'auto') {
            return rtrim($configured, '/');
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = self::$basePath;

        return $scheme . '://' . $host . ($path === '' ? '' : $path);
    }

    private static function detectBasePathFromServer(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/index.php';
        $script = str_replace('\\', '/', (string) $script);
        $dir = dirname($script);
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            return '';
        }

        return rtrim(str_replace('\\', '/', $dir), '/');
    }

    private static function pathFromConfiguredUrl(mixed $url): ?string
    {
        if (!is_string($url) || $url === '' || strtolower($url) === 'auto') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path === '/') {
            return '';
        }

        return rtrim($path, '/');
    }
}
