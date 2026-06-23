<?php
$title = 'Tickets - Soporte';
ob_start();
?>

<div class="page-header">
    <h1 class="page-header__title">Tickets</h1>
    <p class="page-header__subtitle">Listado completo de tickets de soporte</p>
</div>

<div class="card card--flat">
    <?php if (empty($tickets)): ?>
        <p class="card__subtitle" style="margin-bottom: 0;">No hay tickets disponibles.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Problema</th>
                        <th>ISP</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td class="cell-id">#<?= str_pad((string) $ticket['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <span class="cell-client-name"><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido']) ?></span>
                                <span class="cell-client-email"><?= htmlspecialchars($ticket['correo']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($ticket['problema']) ?></td>
                            <td><?= htmlspecialchars(\App\Entities\Ticket::etiquetaIsp($ticket['isp'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $ticket['estado'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
                            <td>
                                <a href="/proyectoWebTicket/soporte/tickets/view?id=<?= $ticket['id'] ?>" class="action-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
