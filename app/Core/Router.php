<?php

namespace App\Core;

use App\Core\Middleware\AuthMiddleware;

/**
 * Enrutador de la aplicación
 */
class Router
{
    private array $routes = [];
    private Request $request;
    private AuthMiddleware $authMiddleware;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Registra una ruta GET
     */
    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registra una ruta POST
     */
    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Añade una ruta al array de rutas
     */
    private function addRoute(string $method, string $path, string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Resuelve la ruta actual
     */
    public function resolve(): void
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $path)) {
                // Verificar middleware de autenticación
                if (!empty($route['middleware'])) {
                    if (!$this->authMiddleware->handle($route['middleware'])) {
                        return;
                    }
                }

                // Ejecutar el controlador
                $this->executeHandler($route['handler']);
                return;
            }
        }

        // Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }

    /**
     * Verifica si la ruta coincide con el path
     */
    private function matchRoute(string $routePath, string $requestPath): bool
    {
        // Normalizar paths (remover barras iniciales/finales)
        $routePath = trim($routePath, '/');
        $requestPath = trim($requestPath, '/');
        
        // Si ambos están vacíos, coinciden
        if ($routePath === '' && $requestPath === '') {
            return true;
        }
        
        // Comparación exacta
        if ($routePath === $requestPath) {
            return true;
        }
        
        // Si la ruta está vacía pero el request no, no coinciden
        if ($routePath === '' || $requestPath === '') {
            return false;
        }
        
        // Comparación por segmentos para permitir flexibilidad
        $routeSegments = explode('/', $routePath);
        $requestSegments = explode('/', $requestPath);
        
        if (count($routeSegments) !== count($requestSegments)) {
            return false;
        }
        
        foreach ($routeSegments as $index => $routeSegment) {
            // Si el segmento de la ruta tiene parámetros {param}, aceptar cualquier valor
            if (preg_match('#^\{[^}]+\}$#', $routeSegment)) {
                continue;
            }
            
            // Comparación exacta de segmentos
            if ($routeSegment !== $requestSegments[$index]) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Ejecuta el handler del controlador
     */
    private function executeHandler(string $handler): void
    {
        list($controllerName, $method) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo "Controlador no encontrado";
            return;
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            http_response_code(404);
            echo "Método no encontrado";
            return;
        }

        $controller->$method($this->request);
    }
}
