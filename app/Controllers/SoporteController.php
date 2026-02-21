<?php

namespace App\Controllers;

use App\Entities\Mensaje;
use App\Models\TicketModel;
use App\Models\MensajeModel;

/**
 * Controlador para soporte
 */
class SoporteController extends BaseController
{
    private TicketModel $ticketModel;
    private MensajeModel $mensajeModel;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new TicketModel();
        $this->mensajeModel = new MensajeModel();
    }

    /**
     * Dashboard de soporte
     */
    public function dashboard(): void
    {
        $user = $this->getCurrentUser();
        
        // Paginación
        $limit = 10;
        $page = (int) ($this->request->get('page') ?? 1);
        $page = max(1, $page); // Asegurar que sea al menos 1
        $offset = ($page - 1) * $limit;

        $totalTickets = $this->ticketModel->countAll();
        $totalPages = ceil($totalTickets / $limit);
        
        // Asegurar que la página no exceda el total
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $tickets = $this->ticketModel->findPaginated($limit, $offset);

        $this->view('soporte/dashboard', [
            'user' => $user,
            'tickets' => $tickets,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }

    /**
     * Lista de tickets
     */
    public function tickets(): void
    {
        $user = $this->getCurrentUser();
        $tickets = $this->ticketModel->findAll();

        $this->view('soporte/tickets', [
            'user' => $user,
            'tickets' => $tickets
        ]);
    }

    /**
     * Ver un ticket específico
     */
    public function viewTicket(): void
    {
        $user = $this->getCurrentUser();
        $ticketId = (int) $this->request->get('id');

        $ticketData = $this->ticketModel->findById($ticketId);

        if (!$ticketData) {
            $this->redirect('/proyectoWebTicket/soporte/tickets');
            return;
        }

        // Pasar el array directamente a la vista
        $mensajes = $this->mensajeModel->findByTicketId($ticketId);

        $this->view('soporte/view_ticket', [
            'user' => $user,
            'ticket' => $ticketData,
            'mensajes' => $mensajes
        ]);
    }

    /**
     * Responde a un ticket
     */
    public function responderTicket(): void
    {
        $user = $this->getCurrentUser();
        $ticketId = (int) $this->request->post('ticket_id');
        $mensaje = $this->request->post('mensaje');

        if (empty($mensaje)) {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
            return;
        }

        $mensajeEntity = new Mensaje();
        $mensajeEntity->setTicketId($ticketId);
        $mensajeEntity->setUsuarioId($user['id']);
        $mensajeEntity->setMensaje($mensaje);

        if ($this->mensajeModel->create($mensajeEntity)) {
            // Asignar el ticket al soporte si aún no está asignado
            $ticket = $this->ticketModel->findByIdEntity($ticketId);
            if ($ticket && !$ticket->getSoporteId()) {
                $this->ticketModel->updateEstado($ticketId, 'en_proceso', $user['id']);
            }
        }

        $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
    }

    /**
     * Cambia el estado de un ticket
     */
    public function cambiarEstado(): void
    {
        $user = $this->getCurrentUser();
        $ticketId = (int) $this->request->post('ticket_id');
        $estado = $this->request->post('estado');

        $estadosValidos = ['abierto', 'en_proceso', 'cerrado'];
        if (!in_array($estado, $estadosValidos)) {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
            return;
        }

        $this->ticketModel->updateEstado($ticketId, $estado, $user['id']);
        $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
    }
}
