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
     * Muestra el formulario de login
     */
    public function login(): void
    {
        if ($this->session->has('user')) {
            $user = $this->getCurrentUser();
            $this->redirect(Usuario::rutaInicio($user['tipo'] ?? ''));
            return;
        }

        $this->view('auth/login');
    }

    /**
     * Autentica al usuario de la intranet
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

        if (!Usuario::perfilEsValido($usuario->getTipo())) {
            $this->view('auth/login', ['error' => 'El perfil de este usuario no tiene acceso a la intranet']);
            return;
        }

        $this->session->set('user', $usuario->toArray());
        $this->redirect(Usuario::rutaInicio($usuario->getTipo()));
    }

    /**
     * Entrada de la intranet. El formulario público ya no existe.
     */
    public function inicio(): void
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/proyectoWebTicket/login');
            return;
        }

        $this->redirect(Usuario::rutaInicio($user['tipo'] ?? ''));
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
