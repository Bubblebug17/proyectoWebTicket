<?php
$title = 'Dashboard - Soporte';
ob_start();
?>

<div class="card">
    <h2>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h2>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
</div>

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
        
        <!-- Paginación -->
        <div class="pagination" style="display: flex; justify-content: center; margin-top: 20px; gap: 10px;">
            <?php if ($currentPage > 1): ?>
                <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $currentPage - 1 ?>" class="btn">Anterior</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $i ?>" class="btn" style="<?= $i === $currentPage ? 'background-color: #2c3e50;' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="/proyectoWebTicket/soporte/dashboard?page=<?= $currentPage + 1 ?>" class="btn">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
