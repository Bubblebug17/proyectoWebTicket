<?php
$title = 'Dashboard - Soporte';
ob_start();
?>

<div class="page-header">
    <h1 class="page-header__title">Dashboard</h1>
    <p class="page-header__subtitle">Bienvenido, <?= htmlspecialchars($user['nombre']) ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-card__label">Tickets Abiertos</div>
            <div class="stat-card__value"><?= (int) ($stats['abierto'] ?? 0) ?></div>
        </div>
        <div class="stat-card__icon stat-card__icon--open" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-card__label">En Proceso</div>
            <div class="stat-card__value"><?= (int) ($stats['en_proceso'] ?? 0) ?></div>
        </div>
        <div class="stat-card__icon stat-card__icon--progress" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-card__label">Cerrados</div>
            <div class="stat-card__value"><?= (int) ($stats['cerrado'] ?? 0) ?></div>
        </div>
        <div class="stat-card__icon stat-card__icon--closed" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
    </div>
</div>

<div class="card card--flat">
    <h2 class="card__title">Todos los Tickets</h2>
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

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $currentPage - 1 ?>" class="btn btn--ghost btn--sm">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $i ?>" class="btn btn--sm<?= $i === $currentPage ? ' is-active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $currentPage + 1 ?>" class="btn btn--ghost btn--sm">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
