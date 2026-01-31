<?php

namespace App\Entities;

/**
 * Entidad Mensaje (respuestas a tickets)
 */
class Mensaje
{
    private ?int $id;
    private int $ticket_id;
    private int $usuario_id;
    private string $mensaje;
    private string $fecha_creacion;

    public function __construct(
        ?int $id = null,
        int $ticket_id = 0,
        int $usuario_id = 0,
        string $mensaje = '',
        string $fecha_creacion = ''
    ) {
        $this->id = $id;
        $this->ticket_id = $ticket_id;
        $this->usuario_id = $usuario_id;
        $this->mensaje = $mensaje;
        $this->fecha_creacion = $fecha_creacion;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketId(): int
    {
        return $this->ticket_id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuario_id;
    }

    public function getMensaje(): string
    {
        return $this->mensaje;
    }

    public function getFechaCreacion(): string
    {
        return $this->fecha_creacion;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTicketId(int $ticket_id): void
    {
        $this->ticket_id = $ticket_id;
    }

    public function setUsuarioId(int $usuario_id): void
    {
        $this->usuario_id = $usuario_id;
    }

    public function setMensaje(string $mensaje): void
    {
        $this->mensaje = $mensaje;
    }

    public function setFechaCreacion(string $fecha_creacion): void
    {
        $this->fecha_creacion = $fecha_creacion;
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'usuario_id' => $this->usuario_id,
            'mensaje' => $this->mensaje,
            'fecha_creacion' => $this->fecha_creacion
        ];
    }
}
