(function () {
    const input = document.getElementById('archivo');
    const dropzone = document.getElementById('fileDropzone');
    const preview = document.getElementById('preview');
    const previewContent = document.getElementById('preview-content');

    if (!input || !dropzone) {
        return;
    }

    function showPreview(file) {
        if (!file) {
            preview.classList.remove('is-visible');
            previewContent.innerHTML = '';
            return;
        }

        preview.classList.add('is-visible');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewContent.innerHTML = '<img src="' + e.target.result + '" alt="Vista previa">';
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewContent.innerHTML =
                    '<video controls><source src="' + e.target.result + '" type="' + file.type + '"></video>';
            };
            reader.readAsDataURL(file);
        } else {
            previewContent.innerHTML = '<p class="form-hint">' + file.name + '</p>';
        }
    }

    input.addEventListener('change', function () {
        showPreview(this.files[0] || null);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', function (e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            showPreview(files[0]);
        }
    });
})();
