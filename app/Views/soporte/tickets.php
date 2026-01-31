<?php
$title = 'Tickets - Soporte';
ob_start();
?>

<div class="card">
    <h2>Todos los Tickets</h2>
    <?php if (empty($tickets)): ?>
        <p>No hay tickets disponibles.</p>
    <?php else: ?>
        <table>
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
                        <td><?= $ticket['id'] ?></td>
                        <td><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido']) ?><br>
                            <small style="color: #666;"><?= htmlspecialchars($ticket['correo']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($ticket['problema']) ?></td>
                        <td><?= htmlspecialchars($ticket['isp']) ?></td>
                        <td>
                            <span class="badge badge-<?= $ticket['estado'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
                        <td>
                            <a href="/proyectoWebTicket/soporte/tickets/view?id=<?= $ticket['id'] ?>" class="btn">Ver</a>
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
