<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Gerencial de Tickets</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c3e50;
            background: #fff;
            padding: 24px;
        }
        .report-header {
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .report-header h1 {
            font-size: 1.5rem;
            margin-bottom: 6px;
        }
        .report-meta {
            font-size: 0.875rem;
            color: #666;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }
        .summary-item {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 1.25rem;
            color: #2c3e50;
        }
        .summary-item span {
            font-size: 0.8rem;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #2c3e50;
            color: #fff;
            font-weight: 600;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .estado-abierto { color: #2980b9; font-weight: 600; }
        .estado-en_proceso { color: #d68910; font-weight: 600; }
        .estado-cerrado { color: #7f8c8d; font-weight: 600; }
        .print-actions {
            margin-bottom: 20px;
        }
        .btn-print {
            background: #2c3e50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        @media print {
            body { padding: 0; }
            .print-actions { display: none !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
            table { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    </div>

    <div class="report-header">
        <h1>Reporte de tickets — Sucursales Tambo</h1>
        <p class="report-meta">
            Generado: <?= date('d/m/Y H:i') ?>
            <?php if (!empty($fechaDesde) || !empty($fechaHasta)): ?>
                | Periodo:
                <?= !empty($fechaDesde) ? htmlspecialchars($fechaDesde) : 'Inicio' ?>
                —
                <?= !empty($fechaHasta) ? htmlspecialchars($fechaHasta) : 'Hoy' ?>
            <?php else: ?>
                | Periodo: Todos los registros
            <?php endif; ?>
        </p>
    </div>

    <?php
    $conteoEstados = ['abierto' => 0, 'en_proceso' => 0, 'cerrado' => 0];
    foreach ($tickets as $ticket) {
        if (isset($conteoEstados[$ticket['estado']])) {
            $conteoEstados[$ticket['estado']]++;
        }
    }
    ?>

    <div class="summary">
        <div class="summary-item">
            <strong><?= $conteoEstados['abierto'] ?></strong>
            <span>Abiertos (Alta criticidad)</span>
        </div>
        <div class="summary-item">
            <strong><?= $conteoEstados['en_proceso'] ?></strong>
            <span>En Proceso</span>
        </div>
        <div class="summary-item">
            <strong><?= $conteoEstados['cerrado'] ?></strong>
            <span>Cerrados</span>
        </div>
        <div class="summary-item">
            <strong><?= count($tickets) ?></strong>
            <span>Total</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Solicitante</th>
                <th>Sucursal</th>
                <th>Correo</th>
                <th>Tipo</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th>Agente</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; color: #666;">No hay tickets en el rango seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?= (int) $ticket['id'] ?></td>
                        <td><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido']) ?></td>
                        <td><?= htmlspecialchars($ticket['sucursal']) ?></td>
                        <td><?= htmlspecialchars($ticket['correo']) ?></td>
                        <td><?= htmlspecialchars($ticket['tipo_nombre']) ?></td>
                        <td><?= htmlspecialchars($ticket['subtipo_nombre']) ?></td>
                        <td class="estado-<?= htmlspecialchars($ticket['estado']) ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ticket['estado']))) ?>
                        </td>
                        <td><?= htmlspecialchars($ticket['soporte_nombre'] ?? 'Sin asignar') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
