<?php

namespace App\Core\Middleware;

use App\Core\Session;

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

        // Verificar rol si está especificado
        if (isset($middleware['role'])) {
            $user = $this->session->get('user');
            $userRole = $user['tipo'] ?? '';

            if ($userRole !== $middleware['role']) {
                // Redirigir al dashboard correspondiente
                if ($userRole === 'cliente') {
                    header('Location: /proyectoWebTicket/cliente/dashboard');
                } elseif ($userRole === 'soporte') {
                    header('Location: /proyectoWebTicket/soporte/dashboard');
                } else {
                    header('Location: /proyectoWebTicket/login');
                }
                return false;
            }
        }

        return true;
    }
}
