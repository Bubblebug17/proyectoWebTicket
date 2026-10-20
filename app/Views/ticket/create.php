<?php
$title = 'Nuevo ticket de sucursal';
$narrowLayout = true;
ob_start();
?>

<div class="card">
    <h1 class="card__title">Ticket de sucursal</h1>
    <p class="card__subtitle">La incidencia queda a nombre de su usuario de intranet.</p>

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
            <h2 class="form-section__title">Datos del solicitante</h2>
            <div class="detail-grid">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($solicitante['nombre'] ?? '') ?></p>
                <p><strong>Apellido:</strong> <?= htmlspecialchars($solicitante['apellido'] ?? '') ?></p>
                <p><strong>Sucursal:</strong> <?= htmlspecialchars($solicitante['sucursal_etiqueta'] ?? '') ?></p>
            </div>
        </section>

        <section class="form-section">
            <h2 class="form-section__title">Clasificación</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_id">Tipo <span class="required">*</span></label>
                    <select id="tipo_id" name="tipo_id">
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($catalogo ?? [] as $tipo): ?>
                            <option value="<?= (int) $tipo['id'] ?>"
                                <?= (string) ($old['tipo_id'] ?? '') === (string) $tipo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="subtipo_id">Motivo <span class="required">*</span></label>
                    <?php
                    $tipoSeleccionado = (string) ($old['tipo_id'] ?? '');
                    $motivosDelTipo = [];
                    foreach ($catalogo ?? [] as $tipo) {
                        if ((string) $tipo['id'] === $tipoSeleccionado) {
                            $motivosDelTipo = $tipo['subtipos'];
                            break;
                        }
                    }
                    ?>
                    <select id="subtipo_id" name="subtipo_id"
                            data-selected="<?= (int) ($old['subtipo_id'] ?? 0) ?>"
                            <?= $tipoSeleccionado === '' ? 'disabled' : '' ?>>
                        <option value=""><?= $tipoSeleccionado === '' ? 'Seleccione primero el tipo' : 'Seleccione un motivo' ?></option>
                        <?php foreach ($motivosDelTipo as $subtipo): ?>
                            <option value="<?= (int) $subtipo['id'] ?>"
                                    data-tipo="<?= htmlspecialchars($tipoSeleccionado) ?>"
                                <?= (string) ($old['subtipo_id'] ?? '') === (string) $subtipo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subtipo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h2 class="form-section__title">Detalle de la incidencia</h2>
            <div class="form-group">
                <label for="descripcion">Descripción detallada <span class="required">*</span></label>
                <textarea id="descripcion" name="descripcion"
                          placeholder="Describa lo ocurrido en la sucursal, productos o equipos involucrados y desde cuándo sucede"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
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

<script type="application/json" id="catalogo-ticket"><?= json_encode($catalogo ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="/proyectoWebTicket/public/js/file-upload.js"></script>
<script src="/proyectoWebTicket/public/js/form-validation.js?v=sucursales"></script>

<?php
$content = ob_get_clean();
require_once ROOT_PATH . '/app/Views/layout.php';
?>
