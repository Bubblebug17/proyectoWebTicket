<?php

use App\Core\AppUrl;

if (!function_exists('app_url')) {
    /** Ruta absoluta con prefijo detectado (enlaces, formularios, assets). */
    function app_url(string $routeWithQuery = ''): string
    {
        return AppUrl::to($routeWithQuery);
    }
}

if (!function_exists('app_asset')) {
    function app_asset(string $relativePath): string
    {
        return app_url('public/' . ltrim($relativePath, '/'));
    }
}
