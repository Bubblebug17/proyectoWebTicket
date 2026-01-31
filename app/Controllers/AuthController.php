<?php

namespace App\Controllers;

use App\Entities\Usuario;
use App\Models\UsuarioModel;

/**
 * Controlador de autenticación
 */
class AuthController extends BaseController
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Muestra el formulario de login (solo para soporte)
     */
    public function login(): void
    {
        // Si ya está autenticado, redirigir al dashboard de soporte
        if ($this->session->has('user')) {
            $this->redirect('/proyectoWebTicket/soporte/dashboard');
            return;
        }

        $this->view('auth/login');
    }

    /**
     * Autentica al usuario (solo soporte)
     */
    public function authenticate(): void
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if (empty($email) || empty($password)) {
            $this->view('auth/login', ['error' => 'Por favor, completa todos los campos']);
            return;
        }

        $usuario = $this->usuarioModel->verifyCredentials($email, $password);

        if (!$usuario) {
            $this->view('auth/login', ['error' => 'Credenciales incorrectas']);
            return;
        }

        // Solo permitir login de usuarios tipo soporte
        if ($usuario->getTipo() !== 'soporte') {
            $this->view('auth/login', ['error' => 'Acceso denegado']);
            return;
        }

        // Guardar usuario en sesión
        $this->session->set('user', $usuario->toArray());

        // Redirigir al dashboard de soporte
        $this->redirect('/proyectoWebTicket/soporte/dashboard');
    }

    /**
     * Cierra la sesión
     */
    public function logout(): void
    {
        $this->session->destroy();
        $this->redirect('/proyectoWebTicket/login');
    }
}
