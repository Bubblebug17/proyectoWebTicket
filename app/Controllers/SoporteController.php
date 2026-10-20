<?php

namespace App\Controllers;

use App\Entities\Mensaje;
use App\Models\TicketModel;
use App\Models\MensajeModel;
use App\Models\UsuarioModel;

/**
 * Controlador para soporte
 */
class SoporteController extends BaseController
{
    private TicketModel $ticketModel;
    private MensajeModel $mensajeModel;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new TicketModel();
        $this->mensajeModel = new MensajeModel();
        $this->usuarioModel = new UsuarioModel();
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

        $stats = ['abierto' => 0, 'en_proceso' => 0, 'cerrado' => 0];
        foreach ($this->ticketModel->getTicketsByStatusCount() as $row) {
            if (isset($stats[$row['estado']])) {
                $stats[$row['estado']] = (int) $row['total'];
            }
        }

        $this->view('soporte/dashboard', [
            'user' => $user,
            'tickets' => $tickets,
            'stats' => $stats,
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
        $esEncargado = ($user['tipo'] ?? '') === 'responsable_area';
        $tickets = $esEncargado
            ? $this->ticketModel->findByEncargado((int) $user['id'])
            : $this->ticketModel->findAll();

        $this->view('soporte/tickets', [
            'user' => $user,
            'tickets' => $tickets,
            'esEncargado' => $esEncargado,
            'error' => $this->mensajeError($this->request->get('error', '')),
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

        if (!$ticketData || !$this->puedeVerTicket($user, $ticketData)) {
            $destino = '/proyectoWebTicket/soporte/tickets';
            if (($user['tipo'] ?? '') === 'responsable_area') {
                $destino .= '?error=no_asignado';
            }
            $this->redirect($destino);
            return;
        }

        $mensajes = $this->mensajeModel->findByTicketId($ticketId);
        $encargadoArea = null;
        if (($user['tipo'] ?? '') === 'soporte') {
            $encargadoArea = $this->usuarioModel->findEncargadoPorArea((int) $ticketData['tipo_id']);
        }

        $this->view('soporte/view_ticket', [
            'user' => $user,
            'ticket' => $ticketData,
            'mensajes' => $mensajes,
            'puedeGestionar' => $this->puedeGestionarTicket($user, $ticketData),
            'encargadoArea' => $encargadoArea,
            'error' => $this->mensajeError($this->request->get('error', '')),
        ]);
    }

    /**
     * El soporte deriva el ticket al encargado del área que corresponde a su tipo.
     */
    public function derivar(): void
    {
        $ticketId = (int) $this->request->post('ticket_id');
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->redirect('/proyectoWebTicket/soporte/tickets');
            return;
        }

        if ($ticket['estado'] === 'cerrado') {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}&error=ticket_cerrado");
            return;
        }

        $encargado = $this->usuarioModel->findEncargadoPorArea((int) $ticket['tipo_id']);
        if ($encargado === null) {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}&error=sin_encargado");
            return;
        }

        $this->ticketModel->derivar($ticketId, $encargado['id']);
        $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
    }

    /**
     * Responde a un ticket
     */
    public function responderTicket(): void
    {
        $user = $this->getCurrentUser();
        $ticketId = (int) $this->request->post('ticket_id');
        $mensaje = $this->request->post('mensaje');

        $ticket = $this->ticketModel->findById($ticketId);
        if (!$ticket || !$this->puedeGestionarTicket($user, $ticket) || trim((string) $mensaje) === '') {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
            return;
        }

        $mensajeEntity = new Mensaje();
        $mensajeEntity->setTicketId($ticketId);
        $mensajeEntity->setUsuarioId($user['id']);
        $mensajeEntity->setMensaje($mensaje);

        if ($this->mensajeModel->create($mensajeEntity) && $ticket['estado'] === 'abierto') {
            $soporteId = ($user['tipo'] ?? '') === 'soporte' ? (int) $user['id'] : null;
            $this->ticketModel->updateEstado($ticketId, 'en_proceso', 'abierto', $soporteId);
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
        $estadoActual = $this->request->post('estado_actual', '');

        $ticket = $this->ticketModel->findById($ticketId);
        if (
            !$ticket
            || !$this->puedeGestionarTicket($user, $ticket)
            || !TicketModel::estadoEsValido($estado)
            || !TicketModel::estadoEsValido($estadoActual)
        ) {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}&error=estado_invalido");
            return;
        }

        $soporteId = ($user['tipo'] ?? '') === 'soporte' ? (int) $user['id'] : null;
        $actualizado = $this->ticketModel->updateEstado($ticketId, $estado, $estadoActual, $soporteId);

        if (!$actualizado) {
            $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}&error=conflicto_concurrencia");
            return;
        }

        $this->redirect("/proyectoWebTicket/soporte/tickets/view?id={$ticketId}");
    }

    private function puedeVerTicket(array $user, array $ticket): bool
    {
        if (($user['tipo'] ?? '') === 'soporte') {
            return true;
        }

        return $this->ticketAsignadoAlEncargado($user, $ticket);
    }

    private function puedeGestionarTicket(array $user, array $ticket): bool
    {
        if (($user['tipo'] ?? '') === 'soporte') {
            return true;
        }

        return $this->ticketAsignadoAlEncargado($user, $ticket);
    }

    private function ticketAsignadoAlEncargado(array $user, array $ticket): bool
    {
        return ($user['tipo'] ?? '') === 'responsable_area'
            && (int) ($ticket['encargado_id'] ?? 0) === (int) ($user['id'] ?? 0);
    }

    private function mensajeError(string $codigo): ?string
    {
        return match ($codigo) {
            'sin_encargado' => 'No hay un encargado registrado para el área de este ticket.',
            'ticket_cerrado' => 'Un ticket cerrado no se puede derivar.',
            'no_asignado' => 'Ese ticket no está derivado a su área.',
            'estado_invalido' => 'No se pudo cambiar el estado del ticket.',
            'conflicto_concurrencia' => 'Otro usuario actualizó el ticket. Revise el estado actual.',
            default => null,
        };
    }
}
