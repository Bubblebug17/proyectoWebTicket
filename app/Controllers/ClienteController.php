<?php

namespace App\Controllers;

use App\Entities\Ticket;
use App\Entities\Mensaje;
use App\Models\TicketModel;
use App\Models\MensajeModel;

/**
 * Controlador para clientes
 */
class ClienteController extends BaseController
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
     * Dashboard del cliente
     */
    public function dashboard(): void
    {
        $user = $this->getCurrentUser();
        $tickets = $this->ticketModel->findByUsuarioId($user['id']);

        $this->view('cliente/dashboard', [
            'user' => $user,
            'tickets' => $tickets
        ]);
    }

    /**
     * Lista de tickets del cliente
     */
    public function tickets(): void
    {
        $user = $this->getCurrentUser();
        $tickets = $this->ticketModel->findByUsuarioId($user['id']);

        $this->view('cliente/tickets', [
            'user' => $user,
            'tickets' => $tickets
        ]);
    }

    /**
     * Formulario para crear un ticket
     */
    public function createTicket(): void
    {
        $user = $this->getCurrentUser();
        $this->view('cliente/create_ticket', ['user' => $user]);
    }

    /**
     * Guarda un nuevo ticket
     */
    public function storeTicket(): void
    {
        $user = $this->getCurrentUser();
        $titulo = $this->request->post('titulo');
        $descripcion = $this->request->post('descripcion');

        if (empty($titulo) || empty($descripcion)) {
            $this->view('cliente/create_ticket', [
                'user' => $user,
                'error' => 'Por favor, completa todos los campos'
            ]);
            return;
        }

        $ticket = new Ticket();
        $ticket->setUsuarioId($user['id']);
        $ticket->setTitulo($titulo);
        $ticket->setDescripcion($descripcion);
        $ticket->setEstado('abierto');

        if ($this->ticketModel->create($ticket)) {
            $this->redirect('/proyectoWebTicket/cliente/tickets');
        } else {
            $this->view('cliente/create_ticket', [
                'user' => $user,
                'error' => 'Error al crear el ticket'
            ]);
        }
    }

    /**
     * Ver un ticket específico
     */
    public function viewTicket(): void
    {
        $user = $this->getCurrentUser();
        $ticketId = (int) $this->request->get('id');

        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket || $ticket->getUsuarioId() !== $user['id']) {
            $this->redirect('/proyectoWebTicket/cliente/tickets');
            return;
        }

        $mensajes = $this->mensajeModel->findByTicketId($ticketId);

        $this->view('cliente/view_ticket', [
            'user' => $user,
            'ticket' => $ticket,
            'mensajes' => $mensajes
        ]);
    }
}
