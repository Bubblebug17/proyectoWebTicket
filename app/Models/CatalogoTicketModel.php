<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Catálogo de tipos y motivos de tickets de sucursal.
 */
class CatalogoTicketModel
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Tipos con sus motivos, en el orden definido del catálogo.
     *
     * @return list<array{id: int, codigo: string, nombre: string, subtipos: list<array{id: int, codigo: string, nombre: string}>}>
     */
    public function listarAgrupado(): array
    {
        $tipos = $this->db->query("
            SELECT id, codigo, nombre
            FROM tipos_ticket
            ORDER BY orden, id
        ")->fetchAll(PDO::FETCH_ASSOC);

        $subtipos = $this->db->query("
            SELECT id, tipo_id, codigo, nombre
            FROM subtipos_ticket
            ORDER BY orden, id
        ")->fetchAll(PDO::FETCH_ASSOC);

        $porTipo = [];
        foreach ($subtipos as $subtipo) {
            $tipoId = (int) $subtipo['tipo_id'];
            $porTipo[$tipoId][] = [
                'id' => (int) $subtipo['id'],
                'codigo' => $subtipo['codigo'],
                'nombre' => $subtipo['nombre'],
            ];
        }

        $catalogo = [];
        foreach ($tipos as $tipo) {
            $tipoId = (int) $tipo['id'];
            $catalogo[] = [
                'id' => $tipoId,
                'codigo' => $tipo['codigo'],
                'nombre' => $tipo['nombre'],
                'subtipos' => $porTipo[$tipoId] ?? [],
            ];
        }

        return $catalogo;
    }

    /**
     * @return list<array{id: int, codigo: string, nombre: string, etiqueta: string}>
     */
    public function listarSucursales(): array
    {
        $rows = $this->db->query("
            SELECT id, codigo, nombre
            FROM sucursales
            ORDER BY orden, id
        ")->fetchAll(PDO::FETCH_ASSOC);

        $sucursales = [];
        foreach ($rows as $row) {
            $sucursales[] = [
                'id' => (int) $row['id'],
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'etiqueta' => $row['codigo'] . ' · ' . $row['nombre'],
            ];
        }

        return $sucursales;
    }

    public function subtipoPerteneceATipo(int $subtipoId, int $tipoId): bool
    {
        if ($subtipoId < 1 || $tipoId < 1) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT 1
            FROM subtipos_ticket
            WHERE id = :id AND tipo_id = :tipo_id
        ");
        $stmt->execute([
            'id' => $subtipoId,
            'tipo_id' => $tipoId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
