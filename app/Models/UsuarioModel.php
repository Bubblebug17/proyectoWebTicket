<?php

namespace App\Models;

use App\Core\Database;
use App\Entities\Usuario;
use PDO;

/**
 * Modelo para la entidad Usuario
 */
class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un usuario por email
     */
    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->db->prepare($this->sqlUsuario() . " WHERE u.email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Busca un usuario por ID
     */
    public function findById(int $id): ?Usuario
    {
        $stmt = $this->db->prepare($this->sqlUsuario() . " WHERE u.id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Verifica las credenciales de un usuario
     */
    public function verifyCredentials(string $email, string $password): ?Usuario
    {
        $usuario = $this->findByEmail($email);

        if (!$usuario) {
            return null;
        }

        // Obtener el hash de la base de datos
        $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && password_verify($password, $data['password'])) {
            return $usuario;
        }

        return null;
    }

    /**
     * Mapea un array de datos a una entidad Usuario
     */
    private function mapToEntity(array $data): Usuario
    {
        $usuario = new Usuario();
        $usuario->setId((int) $data['id']);
        $usuario->setNombre($data['nombre']);
        $usuario->setApellido($data['apellido'] ?? '');
        $usuario->setEmail($data['email']);
        $usuario->setCelular($data['celular'] ?? '');
        $usuario->setTipo($data['tipo']);
        $usuario->setSucursalId(isset($data['sucursal_id']) ? (int) $data['sucursal_id'] : null);
        $usuario->setAreaId(isset($data['area_id']) && $data['area_id'] !== null ? (int) $data['area_id'] : null);
        $usuario->setAreaNombre($data['area_nombre'] ?? '');
        $usuario->setSucursalCodigo($data['sucursal_codigo'] ?? '');
        $usuario->setSucursalNombre($data['sucursal_nombre'] ?? '');
        $usuario->setFechaCreacion($data['fecha_creacion']);

        return $usuario;
    }

    private function sqlUsuario(): string
    {
        return "
            SELECT u.*,
                   s.codigo AS sucursal_codigo,
                   s.nombre AS sucursal_nombre,
                   ta.nombre AS area_nombre
            FROM usuarios u
            INNER JOIN sucursales s ON s.id = u.sucursal_id
            LEFT JOIN tipos_ticket ta ON ta.id = u.area_id
        ";
    }

    /**
     * Encargado de área asignado a un tipo de ticket.
     *
     * @return array{id: int, nombre: string, apellido: string, area_nombre: string}|null
     */
    public function findEncargadoPorArea(int $areaId): ?array
    {
        if ($areaId < 1) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT u.id, u.nombre, u.apellido, ta.nombre AS area_nombre
            FROM usuarios u
            INNER JOIN tipos_ticket ta ON ta.id = u.area_id
            WHERE u.tipo = 'responsable_area' AND u.area_id = :area_id
            LIMIT 1
        ");
        $stmt->execute(['area_id' => $areaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'area_nombre' => $row['area_nombre'],
        ];
    }
}
