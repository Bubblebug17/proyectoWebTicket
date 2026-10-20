<?php

namespace App\Controllers;

use App\Entities\Ticket;
use App\Models\CatalogoTicketModel;
use App\Models\TicketModel;
use App\Models\UsuarioModel;
use App\Core\FileUpload;

/**
 * Controlador público para tickets
 */
class TicketController extends BaseController
{
    private TicketModel $ticketModel;
    private CatalogoTicketModel $catalogoModel;
    private UsuarioModel $usuarioModel;
    private FileUpload $fileUpload;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new TicketModel();
        $this->catalogoModel = new CatalogoTicketModel();
        $this->usuarioModel = new UsuarioModel();
        $this->fileUpload = new FileUpload();
    }

    /**
     * Muestra el formulario para crear un ticket
     */
    public function create(): void
    {
        $this->renderForm();
    }

    /**
     * Procesa el formulario y crea el ticket
     */
    public function store(): void
    {
        $solicitante = $this->solicitanteActual();
        if ($solicitante === null) {
            $this->redirect('/proyectoWebTicket/login');
            return;
        }

        $tipoId = (int) $this->request->post('tipo_id', 0);
        $subtipoId = (int) $this->request->post('subtipo_id', 0);
        $descripcion = trim($this->request->post('descripcion', ''));

        $errors = [];

        if ($solicitante['sucursal_etiqueta'] === '') {
            $errors[] = 'Su usuario no tiene sucursal asignada';
        }

        if ($tipoId < 1 || $subtipoId < 1 || !$this->catalogoModel->subtipoPerteneceATipo($subtipoId, $tipoId)) {
            $errors[] = 'Debe seleccionar un tipo y un motivo válidos';
        }

        if (empty($descripcion)) {
            $errors[] = 'La descripción es requerida';
        }

        // Procesar archivo si existe
        $archivo = null;
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $archivo = $this->fileUpload->upload($_FILES['archivo']);
            if (!$archivo) {
                $errors[] = 'Error al subir el archivo. Verifique el formato y tamaño (máx. 10MB)';
            }
        }

        // Si hay errores, mostrar formulario con errores
        if (!empty($errors)) {
            $this->renderForm([
                'errors' => $errors,
                'solicitante' => $solicitante,
                'old' => $this->oldInput($tipoId, $subtipoId, $descripcion),
            ]);
            return;
        }

        $ticket = new Ticket();
        $ticket->setNombre($solicitante['nombre']);
        $ticket->setApellido($solicitante['apellido']);
        $ticket->setCorreo($solicitante['email']);
        $ticket->setCelular($solicitante['celular']);
        $ticket->setSucursal($solicitante['sucursal_etiqueta']);
        $ticket->setTipoId($tipoId);
        $ticket->setSubtipoId($subtipoId);
        $ticket->setDescripcion($descripcion);
        $ticket->setArchivo($archivo);
        $ticket->setEstado('abierto');

        if ($this->ticketModel->create($ticket)) {
            $this->view('ticket/success', [
                'mensaje' => 'Su ticket de sucursal ha sido registrado. El equipo de atención se pondrá en contacto con usted pronto.'
            ]);
        } else {
            $this->renderForm([
                'errors' => ['Error al registrar el ticket. Por favor, intente nuevamente.'],
                'solicitante' => $solicitante,
                'old' => $this->oldInput($tipoId, $subtipoId, $descripcion),
            ]);
        }
    }

    private function renderForm(array $extra = []): void
    {
        $solicitante = $extra['solicitante'] ?? $this->solicitanteActual();
        if ($solicitante === null) {
            $this->redirect('/proyectoWebTicket/login');
            return;
        }

        unset($extra['solicitante']);
        $this->session->set('user', $solicitante);

        $this->view('ticket/create', array_merge([
            'catalogo' => $this->catalogoModel->listarAgrupado(),
            'solicitante' => $solicitante,
            'user' => $solicitante,
        ], $extra));
    }

    /**
     * @return array<string, int|string>|null
     */
    private function solicitanteActual(): ?array
    {
        $sessionUser = $this->getCurrentUser();
        $id = (int) ($sessionUser['id'] ?? 0);
        if ($id < 1) {
            return null;
        }

        $usuario = $this->usuarioModel->findById($id);
        if ($usuario === null) {
            return null;
        }

        return $usuario->toArray();
    }

    /**
     * @return array<string, int|string>
     */
    private function oldInput(int $tipoId, int $subtipoId, string $descripcion): array
    {
        return [
            'tipo_id' => $tipoId,
            'subtipo_id' => $subtipoId,
            'descripcion' => $descripcion,
        ];
    }
}
