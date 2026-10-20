<?php

namespace App\Core;

use App\Core\Router;
use App\Core\Request;

/**
 * Clase principal de la aplicación
 */
class Application
{
    private Router $router;
    private Request $request;

    public function __construct()
    {
        $this->request = new Request();
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
        $intranet = ['roles' => array_keys(\App\Entities\Usuario::PERFILES)];
        $seguimiento = ['roles' => ['soporte', 'responsable_area']];
        $atencion = ['roles' => ['soporte']];
        $reportes = ['roles' => ['soporte']];

        $this->router->get('', 'AuthController@inicio');

        $this->router->get('login', 'AuthController@login');
        $this->router->post('login', 'AuthController@authenticate');
        $this->router->get('logout', 'AuthController@logout');

        $this->router->get('ticket/create', 'TicketController@create', $intranet);
        $this->router->post('ticket/create', 'TicketController@store', $intranet);

        $this->router->get('soporte/dashboard', 'SoporteController@dashboard', $atencion);
        $this->router->get('soporte/tickets', 'SoporteController@tickets', $seguimiento);
        $this->router->get('soporte/tickets/view', 'SoporteController@viewTicket', $seguimiento);
        $this->router->post('soporte/tickets/derivar', 'SoporteController@derivar', $atencion);
        $this->router->post('soporte/tickets/responder', 'SoporteController@responderTicket', $seguimiento);
        $this->router->post('soporte/tickets/cambiar-estado', 'SoporteController@cambiarEstado', $seguimiento);

        $this->router->get('soporte/reportes', 'ReportesController@index', $reportes);
        $this->router->get('reportes/exportarExcel', 'ReportesController@exportarExcel', $reportes);
        $this->router->get('reportes/exportarPDF', 'ReportesController@exportarPDF', $reportes);
    }
}
