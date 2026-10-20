<?php
$title = 'Ticket Enviado';
$narrowLayout = true;
ob_start();
?>

<div class="card success-card">
    <div class="success-card__icon" aria-hidden="true">✓</div>
    <h1 class="card__title">¡Ticket enviado exitosamente!</h1>
    <p class="success-card__message"><?= htmlspecialchars($mensaje) ?></p>
    <a href="<?= htmlspecialchars(app_url('ticket/create')) ?>" class="btn btn--block">Enviar otro ticket</a>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
