<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\TicketModel;

class ReportesController extends BaseController
{
    private TicketModel $ticketModel;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new TicketModel();
    }

    public function index(Request $request): void
    {
        $fechaDesde = trim((string) $request->get('fecha_desde', ''));
        $fechaHasta = trim((string) $request->get('fecha_hasta', ''));

        $ticketsPorEstado = $this->ticketModel->getTicketsByStatusCount();
        $rawTipoData = $this->ticketModel->getTicketsByTipoAndStatusCount();

        $tipos = [];
        foreach ($rawTipoData as $row) {
            if (!in_array($row['tipo'], $tipos, true)) {
                $tipos[] = $row['tipo'];
            }
        }

        $seriesData = [
            'abierto' => array_fill(0, count($tipos), 0),
            'en_proceso' => array_fill(0, count($tipos), 0),
            'cerrado' => array_fill(0, count($tipos), 0)
        ];

        $tipoMap = array_flip($tipos);

        foreach ($rawTipoData as $row) {
            $tipoIndex = $tipoMap[$row['tipo']];
            $estado = $row['estado'];
            if (isset($seriesData[$estado])) {
                $seriesData[$estado][$tipoIndex] = (int) $row['total'];
            }
        }

        $ticketsPorTipoChartData = [
            'categories' => array_values($tipos),
            'series' => [
                ['name' => 'Abierto', 'data' => $seriesData['abierto'], 'color' => '#3498db'],
                ['name' => 'En Proceso', 'data' => $seriesData['en_proceso'], 'color' => '#f39c12'],
                ['name' => 'Cerrado', 'data' => $seriesData['cerrado'], 'color' => '#95a5a6']
            ]
        ];

        $embudoData = [
            ['Abierto', 0],
            ['En Proceso', 0],
            ['Cerrado', 0]
        ];

        foreach ($ticketsPorEstado as $item) {
            if ($item['estado'] === 'abierto') {
                $embudoData[0][1] = (int) $item['total'];
            }
            if ($item['estado'] === 'en_proceso') {
                $embudoData[1][1] = (int) $item['total'];
            }
            if ($item['estado'] === 'cerrado') {
                $embudoData[2][1] = (int) $item['total'];
            }
        }

        $this->view('soporte/reportes', [
            'user' => $this->getCurrentUser(),
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'ticketsPorEstado' => $ticketsPorEstado,
            'ticketsPorTipoChartData' => $ticketsPorTipoChartData,
            'embudoData' => $embudoData
        ]);
    }

    public function exportarExcel(Request $request): void
    {
        $fechaDesde = trim((string) $request->get('fecha_desde', ''));
        $fechaHasta = trim((string) $request->get('fecha_hasta', ''));

        $tickets = $this->ticketModel->findForReport(
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        $filename = 'reporte_tickets_' . date('Y-m-d_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>Nombre</th>';
        echo '<th>Apellido</th>';
        echo '<th>Correo</th>';
        echo '<th>Celular</th>';
        echo '<th>Sucursal</th>';
        echo '<th>Tipo</th>';
        echo '<th>Motivo</th>';
        echo '<th>Descripción</th>';
        echo '<th>Estado</th>';
        echo '<th>Agente Asignado</th>';
        echo '<th>Fecha Creación</th>';
        echo '<th>Última Actualización</th>';
        echo '</tr></thead><tbody>';

        if (empty($tickets)) {
            echo '<tr><td colspan="13">No hay tickets en el rango seleccionado.</td></tr>';
        } else {
            foreach ($tickets as $ticket) {
                echo '<tr>';
                echo '<td>' . (int) $ticket['id'] . '</td>';
                echo '<td>' . htmlspecialchars($ticket['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['apellido']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['correo']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['celular']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['sucursal']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['tipo_nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['subtipo_nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['descripcion']) . '</td>';
                echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $ticket['estado']))) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['soporte_nombre'] ?? 'Sin asignar') . '</td>';
                echo '<td>' . htmlspecialchars($ticket['fecha_creacion']) . '</td>';
                echo '<td>' . htmlspecialchars($ticket['fecha_actualizacion'] ?? '') . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '</body></html>';
        exit;
    }

    public function exportarPDF(Request $request): void
    {
        $fechaDesde = trim((string) $request->get('fecha_desde', ''));
        $fechaHasta = trim((string) $request->get('fecha_hasta', ''));

        $tickets = $this->ticketModel->findForReport(
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        header('Content-Type: text/html; charset=UTF-8');

        require ROOT_PATH . '/app/Views/soporte/reportes_print.php';
        exit;
    }
}
