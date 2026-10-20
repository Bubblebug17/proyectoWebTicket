<?php

namespace App\Controllers;

use App\Core\AppUrl;
use App\Core\Request;
use App\Core\Session;

/**
 * Controlador base
 */
abstract class BaseController
{
    protected Request $request;
    protected Session $session;

    public function __construct()
    {
        $this->request = new Request();
        $this->session = new Session();
    }

    /**
     * Renderiza una vista
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = ROOT_PATH . "/app/Views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$view}");
        }

        require_once $viewPath;
    }

    /**
     * Redirige a una URL
     */
    protected function redirect(string $url): void
    {
        if (preg_match('#^https?://#i', $url)) {
            header("Location: {$url}");
            exit;
        }

        header('Location: ' . AppUrl::to(ltrim($url, '/')));
        exit;
    }

    /**
     * Obtiene el usuario actual de la sesión
     */
    protected function getCurrentUser(): ?array
    {
        return $this->session->get('user');
    }
}
