<?php
$title = 'Crear Ticket de Soporte';
$narrowLayout = true;
ob_start();
?>

<div class="card">
    <h1 class="card__title">Formulario de Ticket de Soporte</h1>
    <p class="card__subtitle">Complete el siguiente formulario para reportar su problema. Nos pondremos en contacto con usted a la brevedad posible.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/proyectoWebTicket/ticket/create" enctype="multipart/form-data" id="ticketForm" class="needs-validation" novalidate>
        <section class="form-section">
            <h2 class="form-section__title">Datos Personales</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre"
                           value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido <span class="required">*</span></label>
                    <input type="text" id="apellido" name="apellido" placeholder="Apellido"
                           value="<?= htmlspecialchars($old['apellido'] ?? '') ?>">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="correo">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com"
                           value="<?= htmlspecialchars($old['correo'] ?? '') ?>" autocomplete="email">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="celular">Celular <span class="required">*</span></label>
                    <input type="tel" id="celular" name="celular" placeholder="999 999 999"
                           value="<?= htmlspecialchars($old['celular'] ?? '') ?>" autocomplete="tel">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h2 class="form-section__title">Detalles Técnicos</h2>
            <div class="form-group">
                <label for="isp">Tipo de conexión <span class="required">*</span></label>
                <select id="isp" name="isp">
                    <option value="">Seleccione un tipo de conexión</option>
                    <?php foreach ($ispOpciones ?? [] as $valor => $etiqueta): ?>
                        <option value="<?= htmlspecialchars($valor) ?>"
                            <?= ($old['isp'] ?? '') === $valor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etiqueta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </section>

        <section class="form-section">
            <h2 class="form-section__title">Descripción del Problema</h2>
            <div class="form-group">
                <label for="problema">Asunto / Problema <span class="required">*</span></label>
                <input type="text" id="problema" name="problema"
                       placeholder="Resumen breve del problema"
                       value="<?= htmlspecialchars($old['problema'] ?? '') ?>">
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción Detallada <span class="required">*</span></label>
                <textarea id="descripcion" name="descripcion"
                          placeholder="Describa el problema con el mayor detalle posible"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                <div class="invalid-feedback"></div>
            </div>
        </section>

        <section class="form-section">
            <h2 class="form-section__title">Evidencia</h2>
            <div class="form-group">
                <div class="file-dropzone" id="fileDropzone">
                    <input type="file" id="archivo" name="archivo" accept="image/*,video/*">
                    <div class="file-dropzone__icon" aria-hidden="true">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <p class="file-dropzone__text">Arrastra archivos aquí o haz clic para seleccionar</p>
                    <p class="file-dropzone__subtext">Soporta imágenes y vídeos (máx. 10 MB)</p>
                    <span class="file-dropzone__link">Seleccionar Archivo</span>
                </div>
                <div class="invalid-feedback"></div>
                <div id="preview" class="file-preview">
                    <div class="file-preview__title">Vista previa</div>
                    <div id="preview-content"></div>
                </div>
            </div>
        </section>

        <button type="submit" class="btn btn--block btn--lg">Enviar Ticket</button>
    </form>
</div>

<script src="/proyectoWebTicket/public/js/file-upload.js"></script>
<script src="/proyectoWebTicket/public/js/form-validation.js"></script>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
