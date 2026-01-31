<?php

namespace App\Controllers;

use App\Entities\Ticket;
use App\Models\TicketModel;
use App\Core\FileUpload;

/**
 * Controlador público para tickets
 */
class TicketController extends BaseController
{
    private TicketModel $ticketModel;
    private FileUpload $fileUpload;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new TicketModel();
        $this->fileUpload = new FileUpload();
    }

    /**
     * Muestra el formulario para crear un ticket
     */
    public function create(): void
    {
        $this->view('ticket/create');
    }

    /**
     * Procesa el formulario y crea el ticket
     */
    public function store(): void
    {
        // Validar campos requeridos
        $nombre = trim($this->request->post('nombre', ''));
        $apellido = trim($this->request->post('apellido', ''));
        $correo = trim($this->request->post('correo', ''));
        $celular = trim($this->request->post('celular', ''));
        $isp = $this->request->post('isp', '');
        $problema = trim($this->request->post('problema', ''));
        $descripcion = trim($this->request->post('descripcion', ''));

        $errors = [];

        // Validaciones
        if (empty($nombre)) {
            $errors[] = 'El nombre es requerido';
        }

        if (empty($apellido)) {
            $errors[] = 'El apellido es requerido';
        }

        if (empty($correo)) {
            $errors[] = 'El correo es requerido';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del correo no es válido';
        }

        if (empty($celular)) {
            $errors[] = 'El celular es requerido';
        }

        if (empty($isp) || !in_array($isp, ['Claro', 'Win', 'Movistar', 'Bitel', 'Fibratel'])) {
            $errors[] = 'Debe seleccionar un ISP válido';
        }

        if (empty($problema)) {
            $errors[] = 'El problema es requerido';
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
            $this->view('ticket/create', [
                'errors' => $errors,
                'old' => [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo,
                    'celular' => $celular,
                    'isp' => $isp,
                    'problema' => $problema,
                    'descripcion' => $descripcion
                ]
            ]);
            return;
        }

        // Crear ticket
        $ticket = new Ticket();
        $ticket->setNombre($nombre);
        $ticket->setApellido($apellido);
        $ticket->setCorreo($correo);
        $ticket->setCelular($celular);
        $ticket->setIsp($isp);
        $ticket->setProblema($problema);
        $ticket->setDescripcion($descripcion);
        $ticket->setArchivo($archivo);
        $ticket->setEstado('abierto');

        if ($this->ticketModel->create($ticket)) {
            $this->view('ticket/success', [
                'mensaje' => 'Su ticket ha sido registrado exitosamente. Nos pondremos en contacto con usted pronto.'
            ]);
        } else {
            $this->view('ticket/create', [
                'errors' => ['Error al registrar el ticket. Por favor, intente nuevamente.'],
                'old' => [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo,
                    'celular' => $celular,
                    'isp' => $isp,
                    'problema' => $problema,
                    'descripcion' => $descripcion
                ]
            ]);
        }
    }
}
