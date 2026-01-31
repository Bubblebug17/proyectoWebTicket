<?php
$title = 'Registrarse';
ob_start();
?>

<div class="card">
    <h2>Registrarse</h2>
    <form method="POST" action="/proyectoWebTicket/register">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo de Usuario:</label>
            <select id="tipo" name="tipo">
                <option value="cliente">Cliente</option>
                <option value="soporte">Soporte</option>
            </select>
        </div>
        <button type="submit" class="btn">Registrarse</button>
    </form>
    <p style="margin-top: 20px;">
        ¿Ya tienes cuenta? <a href="/proyectoWebTicket/login">Inicia sesión aquí</a>
    </p>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
