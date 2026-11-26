/**
 * ZAI - Carrito de Compras
 * Maneja agregar, eliminar, actualizar cantidades vía AJAX
 */

(function () {
    'use strict';

    // ============ AGREGAR AL CARRITO ============
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-add-cart');
        if (!btn) return;
        e.preventDefault();

        const productoId = btn.dataset.id;
        if (!productoId) return;

        fetch(SITE_URL_JS + '/pages/carrito.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + encodeURIComponent(productoId) + '&cantidad=1'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Actualizar contador
                const counter = document.getElementById('cartCount');
                if (counter) {
                    counter.textContent = data.cantidad;
                    counter.style.display = 'flex';
                    counter.style.animation = 'none';
                    setTimeout(function () { counter.style.animation = 'pop 0.4s ease'; }, 10);
                } else {
                    // Crear contador si no existe
                    const cartBtn = document.querySelector('.cart-btn');
                    if (cartBtn) {
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.id = 'cartCount';
                        span.textContent = data.cantidad;
                        cartBtn.appendChild(span);
                    }
                }
                // Animar botón
                btn.classList.add('added');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> ¡Agregado!';
                setTimeout(function () {
                    btn.classList.remove('added');
                    btn.innerHTML = originalHTML;
                }, 1500);
                // Animar carrito (bump)
                if (window.bumpCart) window.bumpCart();
                // Toast
                if (window.ZAIToast) {
                    ZAIToast('success', 'Producto agregado', data.producto + ' añadido al carrito.');
                }
                // Mini confetti en el botón
                if (window.lanzarConfetti && btn) {
                    const rect = btn.getBoundingClientRect();
                    // Solo lanzar confetti pequeño cerca del botón
                    const colors = ['#7BC950', '#FFD23F'];
                    for (let i = 0; i < 8; i++) {
                        const c = document.createElement('div');
                        c.style.cssText = `position:fixed; width:6px; height:6px; left:${rect.left + rect.width/2}px; top:${rect.top}px; background:${colors[i%2]}; border-radius:50%; z-index:9999; pointer-events:none; transition: all 0.8s cubic-bezier(0.16,1,0.3,1);`;
                        document.body.appendChild(c);
                        setTimeout(function() {
                            c.style.transform = `translate(${(Math.random()-0.5)*100}px, ${-30 - Math.random()*40}px) scale(0)`;
                            c.style.opacity = '0';
                        }, 10);
                        setTimeout(function() { c.remove(); }, 900);
                    }
                }
            } else {
                if (window.ZAIToast) ZAIToast('error', 'Error', data.mensaje || 'No se pudo agregar el producto.');
            }
        })
        .catch(function () {
            if (window.ZAIToast) ZAIToast('error', 'Error de conexión', 'Intenta nuevamente.');
        });
    });

    // ============ ACTUALIZAR CANTIDAD EN CARRITO ============
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.qty-btn');
        if (!btn) return;
        e.preventDefault();

        const action = btn.dataset.action; // 'plus' o 'minus'
        const id = btn.dataset.id;
        const input = document.querySelector('.qty-input[data-id="' + id + '"]');
        if (!input) return;

        let qty = parseInt(input.value, 10) || 1;
        if (action === 'plus') qty++;
        else if (action === 'minus' && qty > 1) qty--;

        input.value = qty;

        // Actualizar en servidor
        fetch(SITE_URL_JS + '/pages/carrito.php?action=update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + encodeURIComponent(id) + '&cantidad=' + qty
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Actualizar subtotal del item
                const subtotalEl = document.querySelector('.item-subtotal[data-id="' + id + '"]');
                if (subtotalEl) subtotalEl.textContent = data.subtotal;
                // Actualizar totales
                actualizarTotales(data.total, data.cantidad);
                if (window.ZAIToast) ZAIToast('info', 'Carrito actualizado', 'Cantidad: ' + qty);
            }
        });
    });

    // Cambio manual en input
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('qty-input')) return;
        const id = e.target.dataset.id;
        let qty = parseInt(e.target.value, 10) || 1;
        if (qty < 1) qty = 1;
        e.target.value = qty;

        fetch(SITE_URL_JS + '/pages/carrito.php?action=update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + encodeURIComponent(id) + '&cantidad=' + qty
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                const subtotalEl = document.querySelector('.item-subtotal[data-id="' + id + '"]');
                if (subtotalEl) subtotalEl.textContent = data.subtotal;
                actualizarTotales(data.total, data.cantidad);
            }
        });
    });

    // ============ ELIMINAR DEL CARRITO ============
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove');
        if (!btn) return;
        e.preventDefault();
        if (!confirm('¿Eliminar este producto del carrito?')) return;

        const id = btn.dataset.id;
        fetch(SITE_URL_JS + '/pages/carrito.php?action=remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + encodeURIComponent(id)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                const item = document.querySelector('.carrito-item[data-id="' + id + '"]');
                if (item) {
                    item.style.transition = 'all 0.3s ease';
                    item.style.transform = 'translateX(-100%)';
                    item.style.opacity = '0';
                    setTimeout(function () {
                        item.remove();
                        // Si no quedan items, recargar página para mostrar estado vacío
                        const remaining = document.querySelectorAll('.carrito-item').length;
                        if (remaining === 0) window.location.reload();
                    }, 300);
                }
                actualizarTotales(data.total, data.cantidad);
                if (window.ZAIToast) ZAIToast('success', 'Producto eliminado', 'Se quitó del carrito.');
            }
        });
    });

    // ============ ACTUALIZAR TOTALES UI ============
    function actualizarTotales(total, cantidad) {
        const totalEls = document.querySelectorAll('.cart-total-amount');
        totalEls.forEach(function (el) {
            el.textContent = 'S/ ' + parseFloat(total).toFixed(2);
        });
        const counter = document.getElementById('cartCount');
        if (counter) {
            counter.textContent = cantidad;
            if (cantidad === 0) counter.style.display = 'none';
        }
    }

    // Exponer para otros scripts
    window.actualizarTotalesCarrito = actualizarTotales;
})();
