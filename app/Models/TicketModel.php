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

    private const SELECT_LISTADO = "
        SELECT t.*,
               s.nombre AS soporte_nombre,
               TRIM(CONCAT(e.nombre, ' ', e.apellido)) AS encargado_nombre,
               tt.nombre AS tipo_nombre,
               tt.codigo AS tipo_codigo,
               st.nombre AS subtipo_nombre,
               st.codigo AS subtipo_codigo
        FROM tickets t
        LEFT JOIN usuarios s ON t.soporte_id = s.id
        LEFT JOIN usuarios e ON t.encargado_id = e.id
        INNER JOIN tipos_ticket tt ON t.tipo_id = tt.id
        INNER JOIN subtipos_ticket st ON t.subtipo_id = st.id
    ";

    /**
     * Obtiene todos los tickets (para soporte)
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(self::SELECT_LISTADO . " ORDER BY t.fecha_creacion DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tickets derivados a un encargado de área.
     */
    public function findByEncargado(int $encargadoId): array
    {
        $stmt = $this->db->prepare(self::SELECT_LISTADO . "
            WHERE t.encargado_id = :encargado_id
            ORDER BY t.fecha_creacion DESC
        ");
        $stmt->execute(['encargado_id' => $encargadoId]);

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
        $stmt = $this->db->prepare(self::SELECT_LISTADO . "
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
        $stmt = $this->db->prepare(self::SELECT_LISTADO . " WHERE t.id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        // Convertir a entidad y mantener datos adicionales
        $ticket = $this->mapToEntity($data);
        $result = $ticket->toArray();
        $result['soporte_nombre'] = $data['soporte_nombre'] ?? null;
        $result['encargado_nombre'] = $data['encargado_nombre'] ?? null;
        $result['tipo_nombre'] = $data['tipo_nombre'] ?? null;
        $result['tipo_codigo'] = $data['tipo_codigo'] ?? null;
        $result['subtipo_nombre'] = $data['subtipo_nombre'] ?? null;
        $result['subtipo_codigo'] = $data['subtipo_codigo'] ?? null;

        return $result;
    }

    /**
     * Crea un nuevo ticket
     */
    public function create(Ticket $ticket): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tickets (nombre, apellido, correo, celular, sucursal, tipo_id, subtipo_id, descripcion, archivo, estado, fecha_creacion)
            VALUES (:nombre, :apellido, :correo, :celular, :sucursal, :tipo_id, :subtipo_id, :descripcion, :archivo, :estado, NOW())
        ");

        return $stmt->execute([
            'nombre' => $ticket->getNombre(),
            'apellido' => $ticket->getApellido(),
            'correo' => $ticket->getCorreo(),
            'celular' => $ticket->getCelular(),
            'sucursal' => $ticket->getSucursal(),
            'tipo_id' => $ticket->getTipoId(),
            'subtipo_id' => $ticket->getSubtipoId(),
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
     * Deriva el ticket al encargado de su área. Un ticket cerrado no se deriva.
     */
    public function derivar(int $id, int $encargadoId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tickets
            SET encargado_id = :encargado_id,
                estado = CASE WHEN estado = 'abierto' THEN 'en_proceso' ELSE estado END,
                fecha_actualizacion = NOW()
            WHERE id = :id AND estado <> 'cerrado'
        ");
        $stmt->execute([
            'id' => $id,
            'encargado_id' => $encargadoId,
        ]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Obtiene tickets para reportes con filtro opcional por rango de fechas,
     * ordenados por criticidad (abierto → en_proceso → cerrado).
     */
    public function findForReport(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $sql = self::SELECT_LISTADO . " WHERE 1=1";
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
     * Obtiene conteo de tickets por tipo de atención
     */
    public function getTicketsByTipoCount(): array
    {
        $stmt = $this->db->query("
            SELECT tt.nombre AS tipo, COUNT(*) AS total
            FROM tickets t
            INNER JOIN tipos_ticket tt ON t.tipo_id = tt.id
            GROUP BY tt.id, tt.nombre
            ORDER BY tt.orden
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene conteo de tickets por tipo y estado
     */
    public function getTicketsByTipoAndStatusCount(): array
    {
        $stmt = $this->db->query("
            SELECT tt.nombre AS tipo, t.estado, COUNT(*) AS total
            FROM tickets t
            INNER JOIN tipos_ticket tt ON t.tipo_id = tt.id
            GROUP BY tt.id, tt.nombre, tt.orden, t.estado
            ORDER BY tt.orden, t.estado
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
        $ticket->setSucursal($data['sucursal']);
        $ticket->setTipoId((int) $data['tipo_id']);
        $ticket->setSubtipoId((int) $data['subtipo_id']);
        $ticket->setDescripcion($data['descripcion']);
        $ticket->setArchivo($data['archivo'] ?? null);
        $ticket->setEstado($data['estado']);
        $ticket->setSoporteId($data['soporte_id'] ?? null);
        $ticket->setEncargadoId(isset($data['encargado_id']) && $data['encargado_id'] !== null ? (int) $data['encargado_id'] : null);
        $ticket->setFechaCreacion($data['fecha_creacion']);
        $ticket->setFechaActualizacion($data['fecha_actualizacion'] ?? null);

        return $ticket;
    }
}
