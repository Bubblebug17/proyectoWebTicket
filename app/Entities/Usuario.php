<?php

namespace App\Entities;

/**
 * Entidad Usuario
 */
class Usuario
{
    private ?int $id;
    private string $nombre;
    private string $email;
    private string $password;
    private string $tipo; // 'cliente' o 'soporte'
    private string $fecha_creacion;

    public function __construct(
        ?int $id = null,
        string $nombre = '',
        string $email = '',
        string $password = '',
        string $tipo = 'cliente',
        string $fecha_creacion = ''
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->tipo = $tipo;
        $this->fecha_creacion = $fecha_creacion;
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getTipo(): string
    {
        return $this->tipo;
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

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
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
            'nombre' => $this->nombre,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'fecha_creacion' => $this->fecha_creacion
        ];
    }
}
