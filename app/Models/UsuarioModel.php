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
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email");
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
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Crea un nuevo usuario
     */
    public function create(Usuario $usuario): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password, tipo, fecha_creacion)
            VALUES (:nombre, :email, :password, :tipo, NOW())
        ");

        return $stmt->execute([
            'nombre' => $usuario->getNombre(),
            'email' => $usuario->getEmail(),
            'password' => password_hash($usuario->getPassword(), PASSWORD_DEFAULT),
            'tipo' => $usuario->getTipo()
        ]);
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
        $usuario->setId($data['id']);
        $usuario->setNombre($data['nombre']);
        $usuario->setEmail($data['email']);
        $usuario->setTipo($data['tipo']);
        $usuario->setFechaCreacion($data['fecha_creacion']);

        return $usuario;
    }
}
