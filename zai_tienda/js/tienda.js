/**
 * ZAI - Tienda JS (Filtros, ordenamiento, búsqueda)
 */

(function () {
    'use strict';

    // ============ FILTROS POR CATEGORÍA ============
    const filtroChecks = document.querySelectorAll('.filtro-categoria');
    filtroChecks.forEach(function (el) {
        el.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href) return; // Si es enlace, dejar que navegue
            e.preventDefault();
            filtroChecks.forEach(function (x) { x.classList.remove('active'); });
            this.classList.add('active');
            const cat = this.dataset.cat;
            filtrarProductos({ categoria: cat });
        });
    });

    // ============ ORDENAR ============
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            ordenarProductos(this.value);
        });
    }

    function ordenarProductos(criterio) {
        const grid = document.querySelector('.tienda-grid');
        if (!grid) return;
        const items = Array.from(grid.querySelectorAll('.producto-card'));
        items.sort(function (a, b) {
            if (criterio === 'precio-asc') {
                return parseFloat(a.dataset.precio) - parseFloat(b.dataset.precio);
            } else if (criterio === 'precio-desc') {
                return parseFloat(b.dataset.precio) - parseFloat(a.dataset.precio);
            } else if (criterio === 'popular') {
                return parseInt(b.dataset.vendidos, 10) - parseInt(a.dataset.vendidos, 10);
            } else if (criterio === 'nombre') {
                return a.dataset.nombre.localeCompare(b.dataset.nombre);
            }
            return 0;
        });
        items.forEach(function (item) {
            item.style.opacity = '0';
            grid.appendChild(item);
            setTimeout(function () { item.style.opacity = '1'; }, 50);
        });
    }

    // ============ FILTRO PRECIO ============
    const precioForm = document.getElementById('precioForm');
    if (precioForm) {
        precioForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const min = this.querySelector('[name="min"]').value;
            const max = this.querySelector('[name="max"]').value;
            filtrarProductos({ min: min, max: max });
        });
    }

    function filtrarProductos(filtros) {
        const grid = document.querySelector('.tienda-grid');
        if (!grid) return;
        const items = grid.querySelectorAll('.producto-card');
        let visibles = 0;
        items.forEach(function (item) {
            let mostrar = true;
            if (filtros.categoria && filtros.categoria !== 'todas') {
                if (item.dataset.categoria !== filtros.categoria) mostrar = false;
            }
            if (filtros.min && parseFloat(item.dataset.precio) < parseFloat(filtros.min)) mostrar = false;
            if (filtros.max && parseFloat(item.dataset.precio) > parseFloat(filtros.max)) mostrar = false;
            if (mostrar) {
                item.style.display = '';
                visibles++;
            } else {
                item.style.display = 'none';
            }
        });
        // Actualizar contador
        const counter = document.querySelector('.tienda-resultados strong');
        if (counter) counter.textContent = visibles;
        // Empty state
        const emptyMsg = document.querySelector('.empty-state-tienda');
        if (emptyMsg) emptyMsg.style.display = visibles === 0 ? 'block' : 'none';
    }

    // ============ TOGGLE VISTA (grid/list) ============
    const viewBtns = document.querySelectorAll('.view-toggle button');
    viewBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            viewBtns.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            const grid = document.querySelector('.tienda-grid');
            if (!grid) return;
            if (this.dataset.view === 'list') {
                grid.style.gridTemplateColumns = '1fr';
            } else {
                grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
            }
        });
    });

    // ============ BÚSQUEDA EN VIVO (opcional) ============
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput && searchInput.dataset.live === 'true') {
        let timeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            timeout = setTimeout(function () {
                if (q.length >= 2) {
                    filtrarProductos({ q: q });
                }
            }, 300);
        });
    }
})();
