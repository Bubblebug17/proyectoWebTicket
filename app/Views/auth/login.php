<?php
$title = 'Iniciar Sesión';
ob_start();
?>

<div class="card">
    <h2>Iniciar Sesión</h2>
    <form method="POST" action="/proyectoWebTicket/login">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Iniciar Sesión</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
