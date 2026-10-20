<?php
$title = 'Reportes - Soporte';
ob_start();
?>

<div class="card">
    <h2>Reportes de Tickets</h2>
    <p>Estado y distribución de los tickets de sucursales Tambo.</p>

    <form method="GET" action="<?= htmlspecialchars(app_url('soporte/reportes')) ?>" class="report-filters">
        <div class="form-group">
            <label for="fecha_desde">Fecha desde</label>
            <input type="date" id="fecha_desde" name="fecha_desde"
                   value="<?= htmlspecialchars($fechaDesde ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="fecha_hasta">Fecha hasta</label>
            <input type="date" id="fecha_hasta" name="fecha_hasta"
                   value="<?= htmlspecialchars($fechaHasta ?? '') ?>">
        </div>
        <button type="submit" class="btn">Aplicar filtros</button>
    </form>

    <?php
    $queryFiltros = http_build_query(array_filter([
        'fecha_desde' => $fechaDesde ?? '',
        'fecha_hasta' => $fechaHasta ?? '',
    ]));
    $suffixFiltros = $queryFiltros !== '' ? '?' . $queryFiltros : '';
    ?>

    <div class="export-actions">
        <a href="<?= htmlspecialchars(app_url('reportes/exportarExcel' . $suffixFiltros)) ?>"
           class="btn btn-success btn-export">Exportar a Excel</a>
        <a href="<?= htmlspecialchars(app_url('reportes/exportarPDF' . $suffixFiltros)) ?>"
           class="btn btn-export" target="_blank" rel="noopener">Imprimir Reporte PDF</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Gráfico de Pastel: Distribución por Estado -->
    <div class="card">
        <div id="pieChartContainer" style="width:100%; height:400px;"></div>
    </div>

    <!-- Gráfico de Barras: Tickets por tipo -->
    <div class="card">
        <div id="barChartContainer" style="width:100%; height:400px;"></div>
    </div>
</div>

<!-- Gráfico de Embudo: Flujo de Estados -->
<div class="card">
    <div id="funnelChartContainer" style="width:100%; height:500px;"></div>
</div>

<!-- Highcharts Libraries -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/funnel.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Datos PHP a JS
    const ticketsPorEstado = <?= json_encode(array_map(function($item) {
        return ['name' => ucfirst(str_replace('_', ' ', $item['estado'])), 'y' => (int)$item['total']];
    }, $ticketsPorEstado)) ?>;

    const ticketsPorTipoData = <?= json_encode($ticketsPorTipoChartData) ?>;

    const embudoData = <?= json_encode($embudoData) ?>;

    // 1. Pie Chart (Estado de Tickets)
    Highcharts.chart('pieChartContainer', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Distribución de Tickets por Estado'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
                colors: ['#3498db', '#f39c12', '#95a5a6']
            }
        },
        series: [{
            name: 'Tickets',
            colorByPoint: true,
            data: ticketsPorEstado
        }]
    });

    // 2. Bar Chart (Tickets por tipo de atención y estado)
    Highcharts.chart('barChartContainer', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Tickets por tipo y estado'
        },
        xAxis: {
            categories: ticketsPorTipoData.categories,
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Cantidad de Tickets'
            }
        },
        tooltip: {
            shared: true,
            useHTML: true,
            formatter: function () {
                var s = '<table>';
                var total = 0;
                
                this.points.forEach(function (point) {
                    s += '<tr><td style="color:' + point.series.color + ';padding:2px 5px 2px 0">' + point.series.name + ': </td>' +
                        '<td style="padding:2px 0; text-align: right;"><b>' + point.y + '</b></td></tr>';
                    total += point.y;
                });
                
                s += '<tr><td style="padding-top: 5px; border-top: 1px solid #ddd; font-weight: bold;">Total: </td>' +
                     '<td style="padding-top: 5px; border-top: 1px solid #ddd; font-weight: bold; text-align: right;">' + total + '</td></tr>';
                
                s += '</table>';
                return s;
            }
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: ticketsPorTipoData.series.map(series => ({
            name: series.name,
            data: series.data,
            color: series.color
        }))
    });

    // 3. Funnel Chart (Embudo de Estados)
    Highcharts.chart('funnelChartContainer', {
        chart: {
            type: 'funnel'
        },
        title: {
            text: 'Embudo de Resolución de Tickets'
        },
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b> ({point.y:,.0f})',
                    softConnector: true
                },
                center: ['40%', '50%'],
                neckWidth: '30%',
                width: '80%',
                height: '80%'
            }
        },
        legend: {
            enabled: false
        },
        series: [{
            name: 'Tickets',
            data: embudoData
        }]
    });
});
</script>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
