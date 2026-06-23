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
    public const ESTADOS_VALIDOS = ['abierto', 'en_proceso', 'cerrado'];

    /** @var array<string, list<string>> */
    private const TRANSICIONES_PERMITIDAS = [
        'abierto' => ['en_proceso', 'cerrado'],
        'en_proceso' => ['cerrado'],
        'cerrado' => [],
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public static function estadoEsValido(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_VALIDOS, true);
    }

    public static function transicionEsPermitida(string $estadoActual, string $nuevoEstado): bool
    {
        if (!self::estadoEsValido($estadoActual) || !self::estadoEsValido($nuevoEstado)) {
            return false;
        }

        if ($estadoActual === $nuevoEstado) {
            return true;
        }

        return in_array($nuevoEstado, self::TRANSICIONES_PERMITIDAS[$estadoActual], true);
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
     * Cuenta todos los tickets
     */
    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM tickets");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene tickets paginados
     */
    public function findPaginated(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, s.nombre as soporte_nombre
            FROM tickets t
            LEFT JOIN usuarios s ON t.soporte_id = s.id
            ORDER BY t.fecha_creacion DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
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
     * Actualiza el estado con control de concurrencia optimista (estado esperado).
     */
    public function updateEstado(
        int $id,
        string $nuevoEstado,
        string $estadoEsperado,
        ?int $soporteId = null
    ): bool {
        if (!self::transicionEsPermitida($estadoEsperado, $nuevoEstado)) {
            return false;
        }

        $sql = "UPDATE tickets SET estado = :nuevo_estado, fecha_actualizacion = NOW()";
        $params = [
            'id' => $id,
            'nuevo_estado' => $nuevoEstado,
            'estado_esperado' => $estadoEsperado,
        ];

        if ($soporteId !== null) {
            $sql .= ", soporte_id = :soporte_id";
            $params['soporte_id'] = $soporteId;
        }

        $sql .= " WHERE id = :id AND estado = :estado_esperado";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() === 1;
    }

    /**
     * Obtiene tickets para reportes con filtro opcional por rango de fechas,
     * ordenados por criticidad (abierto → en_proceso → cerrado).
     */
    public function findForReport(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $sql = "
            SELECT t.*, s.nombre as soporte_nombre
            FROM tickets t
            LEFT JOIN usuarios s ON t.soporte_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if ($fechaDesde !== null && $fechaDesde !== '') {
            $sql .= " AND DATE(t.fecha_creacion) >= :fecha_desde";
            $params['fecha_desde'] = $fechaDesde;
        }

        if ($fechaHasta !== null && $fechaHasta !== '') {
            $sql .= " AND DATE(t.fecha_creacion) <= :fecha_hasta";
            $params['fecha_hasta'] = $fechaHasta;
        }

        $sql .= "
            ORDER BY FIELD(t.estado, 'abierto', 'en_proceso', 'cerrado'), t.fecha_creacion DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene conteo de tickets por estado
     */
    public function getTicketsByStatusCount(): array
    {
        $stmt = $this->db->query("
            SELECT estado, COUNT(*) as total
            FROM tickets
            GROUP BY estado
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene conteo de tickets por ISP
     */
    public function getTicketsByISPCount(): array
    {
        $stmt = $this->db->query("
            SELECT isp, COUNT(*) as total
            FROM tickets
            GROUP BY isp
            ORDER BY total DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene conteo de tickets por ISP y Estado
     */
    public function getTicketsByISPAndStatusCount(): array
    {
        $stmt = $this->db->query("
            SELECT isp, estado, COUNT(*) as total
            FROM tickets
            GROUP BY isp, estado
            ORDER BY isp, estado
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
