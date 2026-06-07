// public/js/main.js — Toko Rini Public JS

(function () {
    'use strict';

    // Mobile Nav Toggle
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav   = document.querySelector('.main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            mainNav.classList.toggle('open');
        });
        // Tutup jika klik di luar
        document.addEventListener('click', (e) => {
            if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
                mainNav.classList.remove('open');
            }
        });
    }

    // Smooth reveal saat scroll
    const revealEls = document.querySelectorAll('.product-card, .category-card, .stat-item');
    if ('IntersectionObserver' in window && revealEls.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        revealEls.forEach((el) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            obs.observe(el);
        });
    }
})();
