<?php
$title = 'Tickets - Soporte';
ob_start();
?>

<div class="page-header">
    <h1 class="page-header__title"><?= !empty($esEncargado) ? 'Mis tickets' : 'Tickets' ?></h1>
    <p class="page-header__subtitle"><?= !empty($esEncargado) ? 'Tickets derivados a su área' : 'Listado de tickets de sucursales Tambo' ?></p>
</div>

<div class="card card--flat">
    <?php if (empty($tickets)): ?>
        <p class="card__subtitle" style="margin-bottom: 0;"><?= !empty($esEncargado) ? 'Aún no le han derivado tickets.' : 'No hay tickets disponibles.' ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Sucursal</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <?php if (empty($esEncargado)): ?>
                            <th>Encargado</th>
                        <?php endif; ?>
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
                            <td><?= htmlspecialchars($ticket['sucursal']) ?></td>
                            <td><?= htmlspecialchars($ticket['tipo_nombre']) ?></td>
                            <td><?= htmlspecialchars($ticket['subtipo_nombre']) ?></td>
                            <td>
                                <span class="badge badge-<?= $ticket['estado'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                                </span>
                            </td>
                            <?php if (empty($esEncargado)): ?>
                                <td><?= htmlspecialchars($ticket['encargado_nombre'] ?: 'Sin derivar') ?></td>
                            <?php endif; ?>
                            <td><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(app_url('soporte/tickets/view?id=' . (int) $ticket['id'])) ?>" class="action-link">
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
