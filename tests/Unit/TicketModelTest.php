<?php

namespace Tests\Unit;

use App\Models\TicketModel;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Suite TEPU Objetivo 3 — TicketModel (concurrencia optimista + máquina de estados).
 *
 * Estrategia: inyección de PDO mockeado vía constructor (?PDO $db) para aislar
 * la capa de persistencia sin conectar a MySQL de producción.
 */
class TicketModelTest extends TestCase
{
    /**
     * Escenario feliz: el estado en BD coincide con $estadoEsperado.
     * El UPDATE afecta 1 fila (rowCount = 1) → retorna true.
     */
    public function testUpdateEstadoExitoso(): void
    {
        /** @var PDOStatement&MockObject $stmt */
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'id' => 10,
                'nuevo_estado' => 'en_proceso',
                'estado_esperado' => 'abierto',
                'soporte_id' => 3,
            ])
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        /** @var PDO&MockObject $pdo */
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('UPDATE tickets SET estado = :nuevo_estado'),
                $this->stringContains('soporte_id = :soporte_id'),
                $this->stringContains('WHERE id = :id AND estado = :estado_esperado')
            ))
            ->willReturn($stmt);

        $model = new TicketModel($pdo);

        $this->assertTrue($model->updateEstado(10, 'en_proceso', 'abierto', 3));
    }

    /**
     * Escenario de conflicto: otro técnico cambió el estado antes del UPDATE.
     * rowCount() = 0 → retorna false sin lanzar excepción.
     */
    public function testUpdateEstadoFallaPorConcurrencia(): void
    {
        /** @var PDOStatement&MockObject $stmt */
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'id' => 10,
                'nuevo_estado' => 'cerrado',
                'estado_esperado' => 'en_proceso',
                'soporte_id' => 2,
            ])
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        /** @var PDO&MockObject $pdo */
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $model = new TicketModel($pdo);

        $this->assertFalse($model->updateEstado(10, 'cerrado', 'en_proceso', 2));
    }

    /**
     * Escenario de reglas de negocio: flujo abierto → en_proceso → cerrado permitido;
     * transiciones invertidas o estados fuera de whitelist rechazadas sin tocar PDO.
     */
    public function testTransicionEstadosPermitidos(): void
    {
        // Transiciones válidas
        $this->assertTrue(TicketModel::transicionEsPermitida('abierto', 'en_proceso'));
        $this->assertTrue(TicketModel::transicionEsPermitida('en_proceso', 'cerrado'));
        $this->assertTrue(TicketModel::transicionEsPermitida('abierto', 'cerrado'));
        $this->assertTrue(TicketModel::transicionEsPermitida('abierto', 'abierto'));

        // Transiciones prohibidas
        $this->assertFalse(TicketModel::transicionEsPermitida('cerrado', 'abierto'));
        $this->assertFalse(TicketModel::transicionEsPermitida('cerrado', 'en_proceso'));
        $this->assertFalse(TicketModel::transicionEsPermitida('en_proceso', 'abierto'));

        // Whitelist ENUM
        $this->assertTrue(TicketModel::estadoEsValido('abierto'));
        $this->assertTrue(TicketModel::estadoEsValido('en_proceso'));
        $this->assertTrue(TicketModel::estadoEsValido('cerrado'));
        $this->assertFalse(TicketModel::estadoEsValido('comprometido'));
        $this->assertFalse(TicketModel::estadoEsValido('<script>alert(1)</script>'));

        /** @var PDOStatement&MockObject $stmt */
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->never())->method('execute');

        /** @var PDO&MockObject $pdo */
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->never())->method('prepare');

        $model = new TicketModel($pdo);

        // Reapertura desde cerrado → bloqueada antes del SQL
        $this->assertFalse($model->updateEstado(1, 'en_proceso', 'cerrado'));
        // Estado fuera de whitelist → bloqueada
        $this->assertFalse($model->updateEstado(1, 'hack', 'abierto'));
        // Retroceso en_proceso → abierto → bloqueada
        $this->assertFalse($model->updateEstado(1, 'abierto', 'en_proceso'));
    }
}
