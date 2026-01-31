<?php
$title = 'Ver Ticket';
ob_start();
?>

<div class="card">
    <h2>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['problema']) ?></h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div>
            <h3>Información del Cliente</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($ticket['correo']) ?></p>
            <p><strong>Celular:</strong> <?= htmlspecialchars($ticket['celular']) ?></p>
            <p><strong>ISP:</strong> <?= htmlspecialchars($ticket['isp']) ?></p>
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

    <div style="margin-top: 20px;">
        <h3>Problema:</h3>
        <p style="font-weight: 600;"><?= htmlspecialchars($ticket['problema']) ?></p>
    </div>

    <div style="margin-top: 20px;">
        <h3>Descripción Detallada:</h3>
        <p><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></p>
    </div>

    <?php if ($ticket['archivo']): ?>
        <div style="margin-top: 20px;">
            <h3>Archivo Adjunto:</h3>
            <?php
            $fileUrl = '/proyectoWebTicket/public/uploads/' . $ticket['archivo'];
            $extension = strtolower(pathinfo($ticket['archivo'], PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $videoExtensions = ['mp4', 'mpeg', 'mov', 'avi'];
            if (in_array($extension, $imageExtensions)):
            ?>
                <img src="<?= $fileUrl ?>" alt="Imagen adjunta" style="max-width: 100%; max-height: 500px; border-radius: 4px; margin-top: 10px;">
            <?php elseif (in_array($extension, $videoExtensions)): ?>
                <video controls style="max-width: 100%; max-height: 500px; border-radius: 4px; margin-top: 10px;">
                    <source src="<?= $fileUrl ?>" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            <?php else: ?>
                <a href="<?= $fileUrl ?>" target="_blank" class="btn">Descargar Archivo</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <form method="POST" action="/proyectoWebTicket/soporte/tickets/cambiar-estado" style="display: inline-block;">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <label><strong>Cambiar Estado:</strong></label>
            <select name="estado" onchange="this.form.submit()" style="margin-left: 10px;">
                <option value="abierto" <?= $ticket['estado'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                <option value="en_proceso" <?= $ticket['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                <option value="cerrado" <?= $ticket['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
            </select>
        </form>
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
    
    <div style="margin-top: 20px;">
        <h3>Responder:</h3>
        <form method="POST" action="/proyectoWebTicket/soporte/tickets/responder">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <div class="form-group">
                <textarea name="mensaje" required placeholder="Escribe tu respuesta..."></textarea>
            </div>
            <button type="submit" class="btn btn-success">Enviar Respuesta</button>
        </form>
    </div>
</div>

<div style="margin-top: 20px;">
    <a href="/proyectoWebTicket/soporte/tickets" class="btn">Volver a Tickets</a>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
