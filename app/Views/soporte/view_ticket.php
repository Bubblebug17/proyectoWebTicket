<?php
$title = 'Ver Ticket';
ob_start();
?>

<div class="card">
    <h1 class="card__title">Ticket #<?= str_pad((string) $ticket['id'], 3, '0', STR_PAD_LEFT) ?></h1>
    <p class="card__subtitle"><?= htmlspecialchars($ticket['problema']) ?></p>

    <div class="detail-grid">
        <div>
            <h3>Información del Cliente</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($ticket['correo']) ?></p>
            <p><strong>Celular:</strong> <?= htmlspecialchars($ticket['celular']) ?></p>
            <p><strong>ISP:</strong> <?= htmlspecialchars(\App\Entities\Ticket::etiquetaIsp($ticket['isp'])) ?></p>
        </div>
        <div>
            <h3>Información del Ticket</h3>
            <p><strong>Estado:</strong>
                <span class="badge badge-<?= $ticket['estado'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                </span>
            </p>
            <p><strong>Fecha de creación:</strong> <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
            <?php if ($ticket['fecha_actualizacion']): ?>
                <p><strong>Última actualización:</strong> <?= date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])) ?></p>
            <?php endif; ?>
            <?php if ($ticket['soporte_nombre']): ?>
                <p><strong>Asignado a:</strong> <?= htmlspecialchars($ticket['soporte_nombre']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-block">
        <h3>Descripción detallada</h3>
        <p><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></p>
    </div>

    <?php if ($ticket['archivo']): ?>
        <div class="detail-block">
            <h3>Archivo adjunto</h3>
            <?php
            $fileUrl = '/proyectoWebTicket/public/uploads/' . $ticket['archivo'];
            $extension = strtolower(pathinfo($ticket['archivo'], PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $videoExtensions = ['mp4', 'mpeg', 'mov', 'avi'];
            if (in_array($extension, $imageExtensions)):
            ?>
                <img src="<?= $fileUrl ?>" alt="Imagen adjunta" style="max-width: 100%; max-height: 480px; border-radius: var(--radius-sm); border: 1px solid var(--color-border-light);">
            <?php elseif (in_array($extension, $videoExtensions)): ?>
                <video controls style="max-width: 100%; max-height: 480px; border-radius: var(--radius-sm);">
                    <source src="<?= $fileUrl ?>" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            <?php else: ?>
                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn--sm">Descargar archivo</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/proyectoWebTicket/soporte/tickets/cambiar-estado" class="status-form">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
        <input type="hidden" name="estado_actual" value="<?= htmlspecialchars($ticket['estado']) ?>">
        <label for="estado"><strong>Cambiar estado:</strong></label>
        <select id="estado" name="estado" onchange="this.form.submit()">
            <option value="abierto" <?= $ticket['estado'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
            <option value="en_proceso" <?= $ticket['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
            <option value="cerrado" <?= $ticket['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
        </select>
    </form>
</div>

<div class="card">
    <h2 class="card__title">Mensajes</h2>
    <?php if (empty($mensajes)): ?>
        <p class="card__subtitle" style="margin-bottom: 0;">No hay mensajes aún.</p>
    <?php else: ?>
        <div class="message-list">
            <?php foreach ($mensajes as $mensaje): ?>
                <div class="message-item">
                    <div class="message-item__header">
                        <span>
                            <span class="message-item__author"><?= htmlspecialchars($mensaje['usuario_nombre'] ?? 'Usuario') ?></span>
                            <span class="message-item__meta">(<?= htmlspecialchars($mensaje['usuario_tipo'] ?? '') ?>)</span>
                        </span>
                        <span class="message-item__meta"><?= date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])) ?></span>
                    </div>
                    <div class="message-item__body"><?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 24px;">
        <h3 class="form-section__title">Responder</h3>
        <form method="POST" action="/proyectoWebTicket/soporte/tickets/responder">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <div class="form-group">
                <textarea name="mensaje" required placeholder="Escribe tu respuesta..."></textarea>
            </div>
            <button type="submit" class="btn">Enviar respuesta</button>
        </form>
    </div>
</div>

<p style="margin-top: 8px;">
    <a href="/proyectoWebTicket/soporte/tickets" class="btn btn--ghost">Volver a tickets</a>
</p>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
