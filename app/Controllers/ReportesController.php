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

    public function index(Request $request)
    {
        // Obtener datos para los reportes
        $ticketsPorEstado = $this->ticketModel->getTicketsByStatusCount();
        $ticketsPorISP = $this->ticketModel->getTicketsByISPCount();
        
        // Preparar datos para el gráfico de barras agrupadas por ISP y Estado
        $rawISPData = $this->ticketModel->getTicketsByISPAndStatusCount();
        
        // Obtener todos los ISPs únicos ordenados
        // Nota: Usamos una lista predefinida si queremos mantener el orden o asegurarnos de que todos aparezcan
        // O extraemos dinámicamente de la base de datos
        $isps = [];
        foreach ($rawISPData as $row) {
            if (!in_array($row['isp'], $isps)) {
                $isps[] = $row['isp'];
            }
        }
        sort($isps); // Orden alfabético para consistencia

        // Inicializar datos para series con 0s
        $seriesData = [
            'abierto' => array_fill(0, count($isps), 0),
            'en_proceso' => array_fill(0, count($isps), 0),
            'cerrado' => array_fill(0, count($isps), 0)
        ];
        
        // Mapeo ISP -> índice para acceso rápido
        $ispMap = array_flip($isps);
        
        foreach ($rawISPData as $row) {
            $ispIndex = $ispMap[$row['isp']];
            $estado = $row['estado'];
            if (isset($seriesData[$estado])) {
                $seriesData[$estado][$ispIndex] = (int)$row['total'];
            }
        }

        // Formato final para la vista (Gráfico de Barras Agrupadas)
        $ticketsPorISPChartData = [
            'categories' => array_values($isps),
            'series' => [
                ['name' => 'Abierto', 'data' => $seriesData['abierto'], 'color' => '#3498db'],
                ['name' => 'En Proceso', 'data' => $seriesData['en_proceso'], 'color' => '#f39c12'],
                ['name' => 'Cerrado', 'data' => $seriesData['cerrado'], 'color' => '#95a5a6']
            ]
        ];
        
        // Preparar datos para el embudo (flujo lógico: Abierto -> En Proceso -> Cerrado)
        $embudoData = [
            ['Abierto', 0],
            ['En Proceso', 0],
            ['Cerrado', 0]
        ];

        foreach ($ticketsPorEstado as $item) {
            if ($item['estado'] === 'abierto') $embudoData[0][1] = (int)$item['total'];
            if ($item['estado'] === 'en_proceso') $embudoData[1][1] = (int)$item['total'];
            if ($item['estado'] === 'cerrado') $embudoData[2][1] = (int)$item['total'];
        }

        // Renderizar vista
        $this->view('soporte/reportes', [
            'user' => $_SESSION['user'],
            'ticketsPorEstado' => $ticketsPorEstado,
            'ticketsPorISPChartData' => $ticketsPorISPChartData, // Nueva variable
            'embudoData' => $embudoData
        ]);
    }
}
