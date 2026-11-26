/**
 * ZAI - JavaScript Principal
 * Funciones de interfaz, animaciones, navbar, scroll, toasts
 */

(function () {
    'use strict';

    // ============ MENU MOVIL ============
    const menuToggle = document.getElementById('menuToggle');
    const navbar = document.getElementById('navbar');
    if (menuToggle && navbar) {
        menuToggle.addEventListener('click', function () {
            navbar.classList.toggle('open');
            const icon = this.querySelector('i');
            if (navbar.classList.contains('open')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
        // Cerrar al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!navbar.contains(e.target) && !menuToggle.contains(e.target) && navbar.classList.contains('open')) {
                navbar.classList.remove('open');
                const icon = menuToggle.querySelector('i');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
    }

    // ============ SCROLL HEADER ============
    const header = document.getElementById('header');
    let lastScroll = 0;
    window.addEventListener('scroll', function () {
        const currentScroll = window.pageYOffset;
        if (header) {
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        // Scroll to top button
        const scrollTop = document.getElementById('scrollTop');
        if (scrollTop) {
            if (currentScroll > 400) scrollTop.classList.add('visible');
            else scrollTop.classList.remove('visible');
        }
        lastScroll = currentScroll;
    });

    // ============ SCROLL TOP ============
    const scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============ ANIMACIONES REVEAL ON SCROLL ============
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length > 0) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        reveals.forEach(function (el) { observer.observe(el); });
    }

    // ============ CONTADORES ANIMADOS ============
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (c) { counterObserver.observe(c); });
    }
    function animateCounter(el) {
        const target = parseInt(el.dataset.counter, 10);
        const duration = 1500;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.floor(eased * target);
            el.textContent = value.toLocaleString('es-PE');
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString('es-PE');
        }
        requestAnimationFrame(step);
    }

    // ============ FLASH AUTO CLOSE ============
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(function (f) {
        const close = f.querySelector('.flash-close');
        if (close) {
            close.addEventListener('click', function () { f.remove(); });
        }
        setTimeout(function () {
            f.style.transition = 'all 0.4s ease';
            f.style.transform = 'translateX(120%)';
            f.style.opacity = '0';
            setTimeout(function () { f.remove(); }, 400);
        }, 5000);
    });

    // ============ TOAST NOTIFICATIONS ============
    window.ZAIToast = function (tipo, titulo, mensaje) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast ' + (tipo || 'success');
        const icon = tipo === 'error' ? 'fa-circle-xmark' :
                     tipo === 'info' ? 'fa-circle-info' : 'fa-circle-check';
        toast.innerHTML = `
            <div class="toast-icon"><i class="fa-solid ${icon}"></i></div>
            <div class="toast-text">
                <strong>${titulo}</strong>
                <span>${mensaje}</span>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('fade-out');
            setTimeout(function () { toast.remove(); }, 400);
        }, 3500);
    };

    // ============ NEWSLETTER FORM ============
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = this.querySelector('input');
            if (input && input.value) {
                if (window.ZAIToast) ZAIToast('success', '¡Suscripción exitosa!', 'Te mantendremos informado de nuestras ofertas.');
                input.value = '';
            }
        });
    }

    // ============ COUNTDOWN TIMER (ofertas) ============
    const countdownEls = document.querySelectorAll('[data-countdown]');
    countdownEls.forEach(function (el) {
        const target = new Date(el.dataset.countdown).getTime();
        function update() {
            const now = new Date().getTime();
            const diff = target - now;
            if (diff <= 0) {
                el.innerHTML = '<span>¡La oferta ha terminado!</span>';
                return;
            }
            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);
            el.querySelector('[data-d]').textContent = String(d).padStart(2, '0');
            el.querySelector('[data-h]').textContent = String(h).padStart(2, '0');
            el.querySelector('[data-m]').textContent = String(m).padStart(2, '0');
            el.querySelector('[data-s]').textContent = String(s).padStart(2, '0');
        }
        update();
        setInterval(update, 1000);
    });

    // ============ FAVORITOS ============
    document.querySelectorAll('.producto-fav').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
                icon.style.color = '#E53935';
                if (window.ZAIToast) ZAIToast('success', 'Añadido a favoritos', 'Producto guardado en tu lista.');
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
                icon.style.color = '';
            }
        });
    });

    // ============ SCROLL ANIMATION CARDS ============
    const cards = document.querySelectorAll('.producto-card, .categoria-card, .beneficio');
    if (cards.length > 0 && 'IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    setTimeout(function () {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, i * 50);
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        cards.forEach(function (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            cardObserver.observe(card);
        });
    }
})();

/* =====================================================================
   BodegaViva v2.0 - ANIMACIONES EXTRA Y MICROINTERACCIONES
   ===================================================================== */

(function () {
    'use strict';

    // ============ SCROLL INDICATOR (barra de progreso superior) ============
    const scrollIndicator = document.createElement('div');
    scrollIndicator.className = 'scroll-indicator';
    scrollIndicator.style.width = '0%';
    document.body.appendChild(scrollIndicator);

    window.addEventListener('scroll', function () {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const percent = (scrollTop / docHeight) * 100;
        scrollIndicator.style.width = percent + '%';
    });

    // ============ EFECTO RIPPLE EN BOTONES ============
    document.querySelectorAll('.btn, .btn-admin, .btn-add-cart, .icon-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });

    // ============ PARALLAX SUTIL EN HERO ============
    const heroImage = document.querySelector('.hero-image');
    if (heroImage && window.innerWidth > 768) {
        document.addEventListener('mousemove', function (e) {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            heroImage.style.transform = `translate(${x}px, ${y}px)`;
        });
    }

    // ============ CARGA DE IMÁGENES CON PLACEHOLDER ============
    document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
        const wrapper = img.parentElement;
        if (wrapper && !wrapper.classList.contains('img-loading')) {
            wrapper.classList.add('img-loading');
        }
        img.addEventListener('load', function () {
            this.classList.add('loaded');
            if (wrapper) wrapper.classList.remove('img-loading');
        });
        if (img.complete) {
            img.classList.add('loaded');
            if (wrapper) wrapper.classList.remove('img-loading');
        }
    });

    // ============ ANIMACIÓN CARRITO (bump al agregar) ============
    window.bumpCart = function () {
        const cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
            cartBtn.classList.add('bump');
            setTimeout(function () { cartBtn.classList.remove('bump'); }, 500);
        }
    };

    // ============ CONFETTI EFFECT ============
    window.lanzarConfetti = function () {
        const colors = ['#0E5A3C', '#7BC950', '#FFD23F', '#FB8C00', '#E53935'];
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 0.5 + 's';
            confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
            if (Math.random() > 0.5) {
                confetti.style.borderRadius = '50%';
            }
            document.body.appendChild(confetti);
            setTimeout(function () { confetti.remove(); }, 4000);
        }
    };

    // ============ HEART BEAT AL HACER FAVORITO ============
    document.addEventListener('click', function (e) {
        const fav = e.target.closest('.producto-fav');
        if (!fav) return;
        const icon = fav.querySelector('i');
        if (icon && icon.classList.contains('fa-solid')) {
            fav.classList.add('heartbeat');
            setTimeout(function () { fav.classList.remove('heartbeat'); }, 600);
        }
    });

    // ============ TOAST MEJORADO CON ACCIONES ============
    window.ZAIToastAction = function (tipo, titulo, mensaje, accionTexto, accionUrl) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast ' + (tipo || 'success');
        const icon = tipo === 'error' ? 'fa-circle-xmark' :
                     tipo === 'info' ? 'fa-circle-info' : 'fa-circle-check';
        toast.innerHTML = `
            <div class="toast-icon"><i class="fa-solid ${icon}"></i></div>
            <div class="toast-text" style="flex:1;">
                <strong>${titulo}</strong>
                <span>${mensaje}</span>
            </div>
            ${accionTexto ? `<a href="${accionUrl}" style="background:var(--verde-claro-2); color:white; padding:6px 12px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:600; white-space:nowrap;">${accionTexto}</a>` : ''}
        `;
        container.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('fade-out');
            setTimeout(function () { toast.remove(); }, 400);
        }, 5000);
    };

    // ============ INTERCEPTAR FORMULARIOS PARA MOSTRAR LOADING ============
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (this.checkValidity()) {
                const btn = this.querySelector('button[type="submit"]');
                if (btn && btn.classList.contains('btn-loader')) {
                    btn.classList.add('loading');
                }
            }
        });
    });

    // ============ ANIMACIÓN DE NÚMEROS EN ELEMENTOS data-counter-animation ============
    const animNums = document.querySelectorAll('[data-counter-animation]');
    if (animNums.length > 0) {
        const obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseFloat(el.dataset.counterAnimation);
                    const decimals = parseInt(el.dataset.decimals || 0, 10);
                    const prefix = el.dataset.prefix || '';
                    const suffix = el.dataset.suffix || '';
                    const duration = 1500;
                    const start = performance.now();
                    function step(now) {
                        const progress = Math.min((now - start) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const value = eased * target;
                        el.textContent = prefix + value.toLocaleString('es-PE', {
                            minimumFractionDigits: decimals,
                            maximumFractionDigits: decimals
                        }) + suffix;
                        if (progress < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        animNums.forEach(function (n) { obs.observe(n); });
    }

    // ============ LAZY LOADING CON OBSERVADOR PARA REVEAL ============
    const reveals = document.querySelectorAll('.reveal:not(.visible)');
    if (reveals.length > 0) {
        const obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    setTimeout(function () {
                        entry.target.classList.add('visible');
                    }, i * 50);
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        reveals.forEach(function (el) { obs.observe(el); });
    }

    // ============ BOTÓN SCROLL-TOP CON EFECTO ============
    const scrollTop = document.getElementById('scrollTop');
    if (scrollTop) {
        let lastClick = 0;
        scrollTop.addEventListener('click', function () {
            const now = Date.now();
            if (now - lastClick < 300) {
                // Doble click → ir al inicio del documento
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            lastClick = now;
        });
    }

    // ============ TOOLTIP EN ELEMENTOS data-tooltip ============
    document.querySelectorAll('[data-tooltip]').forEach(function (el) {
        el.style.position = 'relative';
        const tooltip = document.createElement('div');
        tooltip.textContent = el.dataset.tooltip;
        tooltip.style.cssText = `
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%) translateY(-8px);
            background: var(--verde-oscuro); color: white; padding: 6px 12px;
            border-radius: 6px; font-size: 12px; white-space: nowrap;
            opacity: 0; pointer-events: none; transition: opacity 0.2s;
            z-index: 1000;
        `;
        el.appendChild(tooltip);
        el.addEventListener('mouseenter', function () { tooltip.style.opacity = '1'; });
        el.addEventListener('mouseleave', function () { tooltip.style.opacity = '0'; });
    });
})();
