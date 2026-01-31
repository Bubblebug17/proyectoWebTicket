<?php
$title = 'Ver Ticket';
ob_start();
?>

<div class="card">
    <h2><?= htmlspecialchars($ticket->getTitulo()) ?></h2>
    <p><strong>Estado:</strong> 
        <span class="badge badge-<?= $ticket->getEstado() ?>">
            <?= ucfirst(str_replace('_', ' ', $ticket->getEstado())) ?>
        </span>
    </p>
    <p><strong>Fecha de creación:</strong> <?= date('d/m/Y H:i', strtotime($ticket->getFechaCreacion())) ?></p>
    <div style="margin-top: 20px;">
        <h3>Descripción:</h3>
        <p><?= nl2br(htmlspecialchars($ticket->getDescripcion())) ?></p>
    </div>
</div>

<div class="card">
    <h2>Mensajes</h2>
    <?php if (empty($mensajes)): ?>
        <p>No hay mensajes aún.</p>
    <?php else: ?>
        <?php foreach ($mensajes as $mensaje): ?>
            <div style="padding: 15px; border-bottom: 1px solid #ddd; margin-bottom: 10px;">
                <p><strong><?= htmlspecialchars($mensaje['usuario_nombre'] ?? 'Usuario') ?></strong> 
                <small>(<?= htmlspecialchars($mensaje['usuario_tipo'] ?? '') ?>)</small>
                <span style="float: right; color: #666;">
                    <?= date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])) ?>
                </span></p>
                <p><?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 20px;">
    <a href="/proyectoWebTicket/cliente/tickets" class="btn">Volver a Tickets</a>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
