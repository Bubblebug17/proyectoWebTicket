<?php
$title = 'Mis Tickets';
ob_start();
?>

<div class="card">
    <h2>Mis Tickets</h2>
    <div style="margin-bottom: 20px;">
        <a href="/proyectoWebTicket/cliente/tickets/create" class="btn btn-success">Crear Nuevo Ticket</a>
    </div>
    <?php if (empty($tickets)): ?>
        <p>No tienes tickets aún.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?= $ticket->getId() ?></td>
                        <td><?= htmlspecialchars($ticket->getTitulo()) ?></td>
                        <td>
                            <span class="badge badge-<?= $ticket->getEstado() ?>">
                                <?= ucfirst(str_replace('_', ' ', $ticket->getEstado())) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($ticket->getFechaCreacion())) ?></td>
                        <td>
                            <a href="/proyectoWebTicket/cliente/tickets/view?id=<?= $ticket->getId() ?>" class="btn">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
