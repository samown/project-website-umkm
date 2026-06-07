// public/js/admin.js — Toko Rini Admin JS

(function () {
    'use strict';

    // Auto-dismiss alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // Auto-generate slug dari nama input
    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.querySelector('input[name="slug"]');
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', () => {
            if (slugInput.dataset.manual) return;
            slugInput.value = makeSlug(nameInput.value);
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.manual = '1';
        });
    }

    function makeSlug(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s-]+/g, '-');
    }

    // Preview gambar sebelum upload
    const imageInput = document.querySelector('input[type="file"][name="image"]');
    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                let preview = document.querySelector('.upload-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'upload-preview preview-img';
                    preview.style.marginTop = '8px';
                    this.insertAdjacentElement('afterend', preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
})();
