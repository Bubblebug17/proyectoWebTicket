<?php
$title = 'Crear Ticket';
ob_start();
?>

<div class="card">
    <h2>Crear Nuevo Ticket</h2>
    <form method="POST" action="/proyectoWebTicket/cliente/tickets/create">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Crear Ticket</button>
        <a href="/proyectoWebTicket/cliente/tickets" class="btn" style="margin-left: 10px;">Cancelar</a>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
