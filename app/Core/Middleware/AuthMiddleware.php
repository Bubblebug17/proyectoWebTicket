<?php

namespace App\Core\Middleware;

use App\Core\Session;
use App\Entities\Usuario;

/**
 * Middleware para verificar autenticación y roles
 */
class AuthMiddleware
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Maneja la verificación de autenticación
     */
    public function handle(array $middleware): bool
    {
        // Verificar si el usuario está autenticado
        if (!$this->session->has('user')) {
            header('Location: /proyectoWebTicket/login');
            return false;
        }

        $user = $this->session->get('user');
        $userRole = $user['tipo'] ?? '';
        $permitidos = $middleware['roles'] ?? null;
        if ($permitidos === null && isset($middleware['role'])) {
            $permitidos = [$middleware['role']];
        }

        if (is_array($permitidos) && !in_array($userRole, $permitidos, true)) {
            header('Location: ' . Usuario::rutaInicio($userRole));
            return false;
        }

        return true;
    }
}
