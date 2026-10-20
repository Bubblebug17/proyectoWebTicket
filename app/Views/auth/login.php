<?php
$title = 'Iniciar Sesión';
$narrowLayout = true;
ob_start();
?>

<div class="card auth-card">
    <img class="auth-logo" src="<?= htmlspecialchars(app_asset('img/tambo-logo.webp')) ?>" alt="Tambo" width="1000" height="360">
    <h1 class="card__title">Iniciar Sesión</h1>
    <p class="card__subtitle">Ingreso a la intranet. El registro de tickets no está publicado.</p>
    <form method="POST" action="<?= htmlspecialchars(app_url('login')) ?>">
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn--block btn--lg">Iniciar Sesión</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
