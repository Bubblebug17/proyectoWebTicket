<?php
$title = 'Ticket Enviado';
ob_start();
?>

<div class="card" style="text-align: center; max-width: 600px; margin: 50px auto;">
    <div style="font-size: 64px; color: #27ae60; margin-bottom: 20px;">✓</div>
    <h2 style="color: #27ae60;">¡Ticket Enviado Exitosamente!</h2>
    <p style="font-size: 18px; margin: 20px 0;">
        <?= htmlspecialchars($mensaje) ?>
    </p>
    <div style="margin-top: 30px;">
        <a href="/proyectoWebTicket" class="btn">Enviar Otro Ticket</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
