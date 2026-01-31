<?php

namespace App\Entities;

/**
 * Entidad Ticket
 */
class Ticket
{
    private ?int $id;
    private string $nombre;
    private string $apellido;
    private string $correo;
    private string $celular;
    private string $isp; // 'Claro', 'Win', 'Movistar', 'Bitel', 'Fibratel'
    private string $problema;
    private string $descripcion;
    private ?string $archivo;
    private string $estado; // 'abierto', 'en_proceso', 'cerrado'
    private ?int $soporte_id;
    private string $fecha_creacion;
    private ?string $fecha_actualizacion;

    public function __construct(
        ?int $id = null,
        string $nombre = '',
        string $apellido = '',
        string $correo = '',
        string $celular = '',
        string $isp = '',
        string $problema = '',
        string $descripcion = '',
        ?string $archivo = null,
        string $estado = 'abierto',
        ?int $soporte_id = null,
        string $fecha_creacion = '',
        ?string $fecha_actualizacion = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->celular = $celular;
        $this->isp = $isp;
        $this->problema = $problema;
        $this->descripcion = $descripcion;
        $this->archivo = $archivo;
        $this->estado = $estado;
        $this->soporte_id = $soporte_id;
        $this->fecha_creacion = $fecha_creacion;
        $this->fecha_actualizacion = $fecha_actualizacion;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }

    public function getCelular(): string
    {
        return $this->celular;
    }

    public function getIsp(): string
    {
        return $this->isp;
    }

    public function getProblema(): string
    {
        return $this->problema;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getSoporteId(): ?int
    {
        return $this->soporte_id;
    }

    public function getFechaCreacion(): string
    {
        return $this->fecha_creacion;
    }

    public function getFechaActualizacion(): ?string
    {
        return $this->fecha_actualizacion;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }

    public function setCorreo(string $correo): void
    {
        $this->correo = $correo;
    }

    public function setCelular(string $celular): void
    {
        $this->celular = $celular;
    }

    public function setIsp(string $isp): void
    {
        $this->isp = $isp;
    }

    public function setProblema(string $problema): void
    {
        $this->problema = $problema;
    }

    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setArchivo(?string $archivo): void
    {
        $this->archivo = $archivo;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function setSoporteId(?int $soporte_id): void
    {
        $this->soporte_id = $soporte_id;
    }

    public function setFechaCreacion(string $fecha_creacion): void
    {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function setFechaActualizacion(?string $fecha_actualizacion): void
    {
        $this->fecha_actualizacion = $fecha_actualizacion;
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'correo' => $this->correo,
            'celular' => $this->celular,
            'isp' => $this->isp,
            'problema' => $this->problema,
            'descripcion' => $this->descripcion,
            'archivo' => $this->archivo,
            'estado' => $this->estado,
            'soporte_id' => $this->soporte_id,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }
}
