/**
 * Validación inline integrada en el DOM (sin alert() nativo).
 * Aplica a formularios con clase .needs-validation
 */
(function () {
    'use strict';

    const FIELD_RULES = {
        descripcion: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'La descripción es obligatoria';
                if (trimmed.length < 15) return 'La descripción debe tener al menos 15 caracteres';
                return '';
            }
        },
        tipo_id: {
            validate(value) {
                if (!value) return 'Debe seleccionar un tipo';
                const catalogo = leerCatalogo();
                const existe = catalogo.some(function (tipo) {
                    return String(tipo.id) === String(value);
                });
                if (catalogo.length && !existe) return 'Seleccione un tipo válido';
                return '';
            }
        },
        subtipo_id: {
            validate(value, form) {
                const tipoId = form.elements.tipo_id ? form.elements.tipo_id.value : '';
                if (!tipoId) return 'Seleccione primero el tipo';
                if (!value) return 'Debe seleccionar un motivo';
                const catalogo = leerCatalogo();
                const tipo = catalogo.find(function (item) {
                    return String(item.id) === String(tipoId);
                });
                if (!tipo) return 'Seleccione un motivo válido para el tipo';
                const existe = (tipo.subtipos || []).some(function (subtipo) {
                    return String(subtipo.id) === String(value);
                });
                if (!existe) return 'El motivo no corresponde al tipo seleccionado';
                return '';
            }
        }
    };

    function leerCatalogo() {
        const nodo = document.getElementById('catalogo-ticket');
        if (!nodo) return [];
        try {
            const data = JSON.parse(nodo.textContent || '[]');
            return Array.isArray(data) ? data : [];
        } catch (error) {
            return [];
        }
    }

    function filtrarMotivos(form) {
        const tipo = form.elements.tipo_id;
        const motivo = form.elements.subtipo_id;
        if (!tipo || !motivo) return;

        const catalogo = leerCatalogo();
        if (!catalogo.length) return;

        const tipoId = tipo.value;
        const seleccionado = motivo.value || motivo.dataset.selected || '';
        const grupo = catalogo.find(function (item) {
            return String(item.id) === String(tipoId);
        });

        motivo.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = tipoId ? 'Seleccione un motivo' : 'Seleccione primero el tipo';
        motivo.appendChild(placeholder);

        if (!grupo) {
            motivo.disabled = true;
            return;
        }

        motivo.disabled = false;
        (grupo.subtipos || []).forEach(function (subtipo) {
            const option = document.createElement('option');
            option.value = String(subtipo.id);
            option.setAttribute('data-tipo', String(tipoId));
            option.textContent = subtipo.nombre;
            if (String(subtipo.id) === String(seleccionado)) {
                option.selected = true;
            }
            motivo.appendChild(option);
        });
    }

    function getFeedbackElement(field) {
        const group = field.closest('.form-group');
        if (!group) return null;
        return group.querySelector('.invalid-feedback');
    }

    function setFieldValidity(field, message) {
        const feedback = getFeedbackElement(field);
        const isInvalid = Boolean(message);

        field.classList.toggle('is-invalid', isInvalid);
        field.classList.toggle('is-valid', !isInvalid && field.value.trim() !== '');

        if (feedback) {
            feedback.textContent = message;
        }
    }

    function validateField(field) {
        const rule = FIELD_RULES[field.name];
        if (!rule) return true;

        const message = rule.validate(field.value, field.form);
        setFieldValidity(field, message);
        return message === '';
    }

    function validateForm(form) {
        let isValid = true;
        let firstInvalid = null;

        Object.keys(FIELD_RULES).forEach(function (fieldName) {
            const field = form.elements[fieldName];
            if (!field) return;

            const fieldValid = validateField(field);
            if (!fieldValid) {
                isValid = false;
                if (!firstInvalid) firstInvalid = field;
            }
        });

        const fileInput = form.elements.archivo;
        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                setFieldValidity(fileInput, 'El archivo no debe superar 10 MB');
                isValid = false;
                if (!firstInvalid) firstInvalid = fileInput;
            } else {
                setFieldValidity(fileInput, '');
            }
        }

        if (!isValid && firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus({ preventScroll: true });
        }

        return isValid;
    }

    function bindForm(form) {
        form.setAttribute('novalidate', 'novalidate');
        filtrarMotivos(form);

        if (form.elements.tipo_id) {
            form.elements.tipo_id.addEventListener('change', function () {
                if (form.elements.subtipo_id) {
                    form.elements.subtipo_id.dataset.selected = '';
                }
                filtrarMotivos(form);
                if (form.elements.subtipo_id) {
                    validateField(form.elements.subtipo_id);
                }
            });
        }

        Object.keys(FIELD_RULES).forEach(function (fieldName) {
            const field = form.elements[fieldName];
            if (!field) return;

            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid') || field.dataset.touched === 'true') {
                    validateField(field);
                }
            });

            field.addEventListener('blur', function () {
                field.dataset.touched = 'true';
                validateField(field);
            });
        });

        form.addEventListener('submit', function (event) {
            Object.keys(FIELD_RULES).forEach(function (fieldName) {
                const field = form.elements[fieldName];
                if (field) field.dataset.touched = 'true';
            });

            if (!validateForm(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form.needs-validation').forEach(bindForm);
    });
})();
