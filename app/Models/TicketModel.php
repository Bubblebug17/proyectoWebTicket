<?php

namespace App\Models;

use App\Core\Database;
use App\Entities\Ticket;
use PDO;

/**
 * Modelo para la entidad Ticket
 */
class TicketModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los tickets (para soporte)
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("
            SELECT t.*, s.nombre as soporte_nombre
            FROM tickets t
            LEFT JOIN usuarios s ON t.soporte_id = s.id
            ORDER BY t.fecha_creacion DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un ticket por ID con información adicional
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, s.nombre as soporte_nombre
            FROM tickets t
            LEFT JOIN usuarios s ON t.soporte_id = s.id
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        // Convertir a entidad y mantener datos adicionales
        $ticket = $this->mapToEntity($data);
        $result = $ticket->toArray();
        $result['soporte_nombre'] = $data['soporte_nombre'] ?? null;
        
        return $result;
    }

    /**
     * Busca un ticket por ID como entidad
     */
    public function findByIdEntity(int $id): ?Ticket
    {
        $stmt = $this->db->prepare("
            SELECT t.*
            FROM tickets t
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Crea un nuevo ticket
     */
    public function create(Ticket $ticket): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tickets (nombre, apellido, correo, celular, isp, problema, descripcion, archivo, estado, fecha_creacion)
            VALUES (:nombre, :apellido, :correo, :celular, :isp, :problema, :descripcion, :archivo, :estado, NOW())
        ");

        return $stmt->execute([
            'nombre' => $ticket->getNombre(),
            'apellido' => $ticket->getApellido(),
            'correo' => $ticket->getCorreo(),
            'celular' => $ticket->getCelular(),
            'isp' => $ticket->getIsp(),
            'problema' => $ticket->getProblema(),
            'descripcion' => $ticket->getDescripcion(),
            'archivo' => $ticket->getArchivo(),
            'estado' => $ticket->getEstado()
        ]);
    }

    /**
     * Actualiza el estado de un ticket
     */
    public function updateEstado(int $id, string $estado, ?int $soporteId = null): bool
    {
        $sql = "UPDATE tickets SET estado = :estado, fecha_actualizacion = NOW()";
        $params = ['id' => $id, 'estado' => $estado];

        if ($soporteId !== null) {
            $sql .= ", soporte_id = :soporte_id";
            $params['soporte_id'] = $soporteId;
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Mapea un array de datos a una entidad Ticket
     */
    private function mapToEntity(array $data): Ticket
    {
        $ticket = new Ticket();
        $ticket->setId($data['id']);
        $ticket->setNombre($data['nombre']);
        $ticket->setApellido($data['apellido']);
        $ticket->setCorreo($data['correo']);
        $ticket->setCelular($data['celular']);
        $ticket->setIsp($data['isp']);
        $ticket->setProblema($data['problema']);
        $ticket->setDescripcion($data['descripcion']);
        $ticket->setArchivo($data['archivo'] ?? null);
        $ticket->setEstado($data['estado']);
        $ticket->setSoporteId($data['soporte_id'] ?? null);
        $ticket->setFechaCreacion($data['fecha_creacion']);
        $ticket->setFechaActualizacion($data['fecha_actualizacion'] ?? null);

        return $ticket;
    }
}
