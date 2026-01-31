<?php
$title = 'Crear Ticket de Soporte';
ob_start();
?>

<div class="card">
    <h2>Formulario de Ticket de Soporte</h2>
    <p>Complete el siguiente formulario para reportar su problema. Nos pondremos en contacto con usted a la brevedad posible.</p>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/proyectoWebTicket/ticket/create" enctype="multipart/form-data" id="ticketForm">
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="nombre">Nombre: <span style="color: red;">*</span></label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="apellido">Apellido: <span style="color: red;">*</span></label>
                <input type="text" id="apellido" name="apellido" required
                       value="<?= htmlspecialchars($old['apellido'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="correo">Correo Electrónico: <span style="color: red;">*</span></label>
                <input type="email" id="correo" name="correo" required
                       value="<?= htmlspecialchars($old['correo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="celular">Celular: <span style="color: red;">*</span></label>
                <input type="tel" id="celular" name="celular" required
                       value="<?= htmlspecialchars($old['celular'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="isp">ISP (Proveedor de Internet): <span style="color: red;">*</span></label>
            <select id="isp" name="isp" required>
                <option value="">Seleccione un ISP</option>
                <option value="Claro" <?= ($old['isp'] ?? '') === 'Claro' ? 'selected' : '' ?>>Claro</option>
                <option value="Win" <?= ($old['isp'] ?? '') === 'Win' ? 'selected' : '' ?>>Win</option>
                <option value="Movistar" <?= ($old['isp'] ?? '') === 'Movistar' ? 'selected' : '' ?>>Movistar</option>
                <option value="Bitel" <?= ($old['isp'] ?? '') === 'Bitel' ? 'selected' : '' ?>>Bitel</option>
                <option value="Fibratel" <?= ($old['isp'] ?? '') === 'Fibratel' ? 'selected' : '' ?>>Fibratel</option>
            </select>
        </div>

        <div class="form-group">
            <label for="problema">Problema: <span style="color: red;">*</span></label>
            <input type="text" id="problema" name="problema" required
                   placeholder="Resumen breve del problema"
                   value="<?= htmlspecialchars($old['problema'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción Detallada: <span style="color: red;">*</span></label>
            <textarea id="descripcion" name="descripcion" required 
                      placeholder="Describa el problema con el mayor detalle posible"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="archivo">Imagen/Video (Opcional):</label>
            <input type="file" id="archivo" name="archivo" accept="image/*,video/*">
            <small style="display: block; margin-top: 5px; color: #666;">
                Formatos permitidos: JPG, PNG, GIF, MP4, MOV, AVI. Tamaño máximo: 10MB
            </small>
            <div id="preview" style="margin-top: 15px; display: none;">
                <h4>Vista Previa:</h4>
                <div id="preview-content"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Enviar Ticket</button>
    </form>
</div>

<script>
document.getElementById('archivo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    const previewContent = document.getElementById('preview-content');
    
    if (file) {
        preview.style.display = 'block';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContent.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 400px; border-radius: 4px;">';
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContent.innerHTML = '<video controls style="max-width: 100%; max-height: 400px;"><source src="' + e.target.result + '" type="' + file.type + '"></video>';
            };
            reader.readAsDataURL(file);
        }
    } else {
        preview.style.display = 'none';
        previewContent.innerHTML = '';
    }
});
</script>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
