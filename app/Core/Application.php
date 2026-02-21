<?php

namespace App\Core;

use App\Core\Router;
use App\Core\Request;
use App\Core\Session;

/**
 * Clase principal de la aplicación
 */
class Application
{
    private Router $router;
    private Request $request;
    private Session $session;

    public function __construct()
    {
        $this->request = new Request();
        $this->session = new Session();
        $this->router = new Router($this->request);
    }

    /**
     * Ejecuta la aplicación
     */
    public function run(): void
    {
        // Cargar rutas
        $this->loadRoutes();
        
        // Resolver la ruta
        $this->router->resolve();
    }

    /**
     * Carga las rutas de la aplicación
     */
    private function loadRoutes(): void
    {
        // Rutas públicas - Formulario de tickets
        $this->router->get('', 'TicketController@create');
        $this->router->get('ticket/create', 'TicketController@create');
        $this->router->post('ticket/create', 'TicketController@store');

        // Rutas públicas - Autenticación (solo para soporte)
        $this->router->get('login', 'AuthController@login');
        $this->router->post('login', 'AuthController@authenticate');
        $this->router->get('logout', 'AuthController@logout');

        // Rutas protegidas - Soporte
        $this->router->get('soporte/dashboard', 'SoporteController@dashboard', ['role' => 'soporte']);
        $this->router->get('soporte/tickets', 'SoporteController@tickets', ['role' => 'soporte']);
        $this->router->get('soporte/tickets/view', 'SoporteController@viewTicket', ['role' => 'soporte']);
        $this->router->post('soporte/tickets/responder', 'SoporteController@responderTicket', ['role' => 'soporte']);
        $this->router->post('soporte/tickets/cambiar-estado', 'SoporteController@cambiarEstado', ['role' => 'soporte']);
        
        // Rutas protegidas - Reportes
        $this->router->get('soporte/reportes', 'ReportesController@index', ['role' => 'soporte']);
    }
}
