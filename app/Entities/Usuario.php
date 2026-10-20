<?php

namespace App\Entities;

/**
 * Usuario de la intranet de sucursales Tambo.
 */
class Usuario
{
    /** @var array<string, string> */
    public const PERFILES = [
        'responsable_tienda' => 'Responsable de Tienda',
        'soporte' => 'Soporte',
        'responsable_area' => 'Responsable de Área',
    ];

    private ?int $id;
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $celular;
    private string $tipo;
    private ?int $sucursalId;
    private ?int $areaId;
    private string $areaNombre;
    private string $sucursalCodigo;
    private string $sucursalNombre;
    private string $fecha_creacion;

    public function __construct(
        ?int $id = null,
        string $nombre = '',
        string $apellido = '',
        string $email = '',
        string $celular = '',
        string $tipo = 'responsable_tienda',
        ?int $sucursalId = null,
        string $fecha_creacion = ''
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->celular = $celular;
        $this->tipo = $tipo;
        $this->sucursalId = $sucursalId;
        $this->areaId = null;
        $this->areaNombre = '';
        $this->sucursalCodigo = '';
        $this->sucursalNombre = '';
        $this->fecha_creacion = $fecha_creacion;
    }

    public static function perfilEsValido(string $tipo): bool
    {
        return isset(self::PERFILES[$tipo]);
    }

    public static function etiquetaPerfil(string $tipo): string
    {
        return self::PERFILES[$tipo] ?? $tipo;
    }

    public static function rutaInicio(string $tipo): string
    {
        return match ($tipo) {
            'soporte' => '/proyectoWebTicket/soporte/dashboard',
            'responsable_area' => '/proyectoWebTicket/soporte/tickets',
            default => '/proyectoWebTicket/ticket/create',
        };
    }

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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCelular(): string
    {
        return $this->celular;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getSucursalId(): ?int
    {
        return $this->sucursalId;
    }

    public function getAreaId(): ?int
    {
        return $this->areaId;
    }

    public function getAreaNombre(): string
    {
        return $this->areaNombre;
    }

    public function getSucursalCodigo(): string
    {
        return $this->sucursalCodigo;
    }

    public function getSucursalNombre(): string
    {
        return $this->sucursalNombre;
    }

    public function getSucursalEtiqueta(): string
    {
        if ($this->sucursalCodigo === '' || $this->sucursalNombre === '') {
            return $this->sucursalNombre;
        }

        return $this->sucursalCodigo . ' · ' . $this->sucursalNombre;
    }

    public function getFechaCreacion(): string
    {
        return $this->fecha_creacion;
    }

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

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setCelular(string $celular): void
    {
        $this->celular = $celular;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function setSucursalId(?int $sucursalId): void
    {
        $this->sucursalId = $sucursalId;
    }

    public function setAreaId(?int $areaId): void
    {
        $this->areaId = $areaId;
    }

    public function setAreaNombre(string $areaNombre): void
    {
        $this->areaNombre = $areaNombre;
    }

    public function setSucursalCodigo(string $sucursalCodigo): void
    {
        $this->sucursalCodigo = $sucursalCodigo;
    }

    public function setSucursalNombre(string $sucursalNombre): void
    {
        $this->sucursalNombre = $sucursalNombre;
    }

    public function setFechaCreacion(string $fecha_creacion): void
    {
        $this->fecha_creacion = $fecha_creacion;
    }

    /**
     * Datos de sesión. No incluye la contraseña.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'celular' => $this->celular,
            'tipo' => $this->tipo,
            'perfil' => self::etiquetaPerfil($this->tipo),
            'sucursal_id' => $this->sucursalId,
            'area_id' => $this->areaId,
            'area_nombre' => $this->areaNombre,
            'sucursal_codigo' => $this->sucursalCodigo,
            'sucursal_nombre' => $this->sucursalNombre,
            'sucursal_etiqueta' => $this->getSucursalEtiqueta(),
            'fecha_creacion' => $this->fecha_creacion,
        ];
    }
}
