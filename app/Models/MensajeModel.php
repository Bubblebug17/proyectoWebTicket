<?php

namespace App\Models;

use App\Core\Database;
use App\Entities\Mensaje;
use PDO;

/**
 * Modelo para la entidad Mensaje
 */
class MensajeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los mensajes de un ticket con información adicional
     */
    public function findByTicketId(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre as usuario_nombre, u.tipo as usuario_tipo
            FROM mensajes m
            INNER JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.ticket_id = :ticket_id
            ORDER BY m.fecha_creacion ASC
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        
        // Devolver directamente los datos con información adicional
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo mensaje
     */
    public function create(Mensaje $mensaje): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO mensajes (ticket_id, usuario_id, mensaje, fecha_creacion)
            VALUES (:ticket_id, :usuario_id, :mensaje, NOW())
        ");

        return $stmt->execute([
            'ticket_id' => $mensaje->getTicketId(),
            'usuario_id' => $mensaje->getUsuarioId(),
            'mensaje' => $mensaje->getMensaje()
        ]);
    }

    /**
     * Mapea un array de datos a una entidad Mensaje
     */
    private function mapToEntity(array $data): Mensaje
    {
        $mensaje = new Mensaje();
        $mensaje->setId($data['id']);
        $mensaje->setTicketId($data['ticket_id']);
        $mensaje->setUsuarioId($data['usuario_id']);
        $mensaje->setMensaje($data['mensaje']);
        $mensaje->setFechaCreacion($data['fecha_creacion']);

        return $mensaje;
    }
}
