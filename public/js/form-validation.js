/**
 * Validación inline integrada en el DOM (sin alert() nativo).
 * Aplica a formularios con clase .needs-validation
 */
(function () {
    'use strict';

    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const CELULAR_REGEX = /^9\d{8}$/;

    const FIELD_RULES = {
        nombre: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'El nombre es obligatorio';
                if (trimmed.length < 3) return 'El nombre debe tener al menos 3 caracteres';
                return '';
            }
        },
        apellido: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'El apellido es obligatorio';
                if (trimmed.length < 3) return 'El apellido debe tener al menos 3 caracteres';
                return '';
            }
        },
        correo: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'El correo es obligatorio';
                if (!EMAIL_REGEX.test(trimmed)) return 'Ingrese un correo electrónico válido';
                return '';
            }
        },
        celular: {
            validate(value) {
                const digits = value.replace(/\s/g, '');
                if (!digits) return 'El celular es obligatorio';
                if (!CELULAR_REGEX.test(digits)) return 'Ingrese un celular peruano válido (9 dígitos, inicia con 9)';
                return '';
            }
        },
        descripcion: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'La descripción es obligatoria';
                if (trimmed.length < 15) return 'La descripción debe tener al menos 15 caracteres';
                return '';
            }
        },
        isp: {
            validate(value) {
                const permitidos = ['fibra_optica', 'wisp_inalambrico'];
                if (!value) return 'Debe seleccionar un ISP';
                if (!permitidos.includes(value)) return 'Seleccione un tipo de proveedor válido';
                return '';
            }
        },
        problema: {
            validate(value) {
                const trimmed = value.trim();
                if (!trimmed) return 'El problema es obligatorio';
                return '';
            }
        }
    };

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

        const message = rule.validate(field.value);
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
