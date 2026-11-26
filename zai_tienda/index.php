<?php
/**
 * BodegaViva - Página de Inicio
 * Tienda de Abarrotes
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_activa = 'inicio';
$titulo = 'Inicio';
$css_extra = ['tienda.css'];
$js_extra = ['cart.js'];

$categorias = obtenerCategorias();
$destacados = obtenerDestacados(8);
$ofertas    = obtenerOfertas(4);
$mas_vendidos = obtenerMasVendidos(8);

include __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-content reveal">
            <span class="hero-badge"><i class="fa-solid fa-truck-fast"></i> Envío rápido en Lima</span>
            <h1>Tu bodega <span class="highlight">siempre cerca</span></h1>
            <p>Tu bodega de confianza ahora en línea. Encuentra los mejores productos de abarrotes, lácteos, bebidas y más, con entrega rápida a domicilio.</p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/pages/tienda.php" class="btn btn-yellow btn-lg">
                    <i class="fa-solid fa-cart-shopping"></i> Comprar ahora
                </a>
                <a href="<?= SITE_URL ?>/pages/ofertas.php" class="btn btn-outline btn-lg" style="color:white;border-color:white;">
                    <i class="fa-solid fa-tags"></i> Ver ofertas
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <strong>+500</strong>
                    <span>Productos</span>
                </div>
                <div class="hero-stat">
                    <strong>+2,500</strong>
                    <span>Clientes felices</span>
                </div>
                <div class="hero-stat">
                    <strong>30 min</strong>
                    <span>Entrega promedio</span>
                </div>
            </div>
        </div>
        <div class="hero-image reveal reveal-delay-2">
            <img src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=700&q=80" alt="Tienda de abarrotes <?= SITE_NAME ?>">
            <div class="hero-floating-card top">
                <div class="ic"><i class="fa-solid fa-bolt"></i></div>
                <div>
                    <strong>Entrega Hoy</strong>
                    <span style="color:var(--gris-texto);font-weight:400;">30 minutos</span>
                </div>
            </div>
            <div class="hero-floating-card bottom">
                <div class="ic"><i class="fa-solid fa-star"></i></div>
                <div>
                    <strong>4.9 / 5</strong>
                    <span style="color:var(--gris-texto);font-weight:400;">2,500 reseñas</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ BENEFICIOS ============ -->
<section class="beneficios">
    <div class="container">
        <div class="beneficios-grid">
            <div class="beneficio reveal">
                <div class="beneficio-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <h3>Envío Rápido</h3>
                <p>Entregamos en 30 minutos dentro de Lima y alrededores.</p>
            </div>
            <div class="beneficio reveal reveal-delay-1">
                <div class="beneficio-icon"><i class="fa-solid fa-tag"></i></div>
                <h3>Mejores Precios</h3>
                <p>Precios justos de bodega, sin recargos ni costos ocultos.</p>
            </div>
            <div class="beneficio reveal reveal-delay-2">
                <div class="beneficio-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Productos Calidad</h3>
                <p>Solo trabajamos con marcas reconocidas y productos frescos.</p>
            </div>
            <div class="beneficio reveal reveal-delay-3">
                <div class="beneficio-icon"><i class="fa-solid fa-headset"></i></div>
                <h3>Atención 24/7</h3>
                <p>Pedidos por WhatsApp en cualquier momento del día.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============ CATEGORÍAS ============ -->
<section class="section">
    <div class="container">
        <div class="section-header center reveal">
            <span class="section-eyebrow">Explora por categoría</span>
            <h2 class="section-title">Nuestras <span class="highlight">Categorías</span></h2>
            <p class="section-subtitle">Encuentra todo lo que necesitas organizado para ti. Calidad y frescura garantizada en cada producto.</p>
        </div>
        <div class="categorias-grid">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= SITE_URL ?>/pages/categoria.php?id=<?= $cat['id'] ?>" class="categoria-card reveal">
                    <div class="categoria-icon">
                        <i class="fa-solid <?= htmlspecialchars($cat['icono'] ?: 'fa-tag') ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($cat['nombre']) ?></h3>
                    <p><?= htmlspecialchars(acortar($cat['descripcion'], 60)) ?></p>
                    <div class="arrow"><i class="fa-solid fa-arrow-right"></i> Ver productos</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ OFERTAS BANNER ============ -->
<section class="section" style="background:var(--gris-claro); padding:60px 0;">
    <div class="container">
        <div class="ofertas-banner reveal">
            <h2><i class="fa-solid fa-fire"></i> Ofertas Especiales del Día</h2>
            <p>Descuentos exclusivos por tiempo limitado. ¡No dejes pasar esta oportunidad!</p>
            <div class="countdown" data-countdown="<?= date('Y-m-d H:i:s', strtotime('+2 days')) ?>">
                <div class="countdown-box"><strong data-d>00</strong><span>días</span></div>
                <div class="countdown-box"><strong data-h>00</strong><span>horas</span></div>
                <div class="countdown-box"><strong data-m>00</strong><span>min</span></div>
                <div class="countdown-box"><strong data-s>00</strong><span>seg</span></div>
            </div>
            <div style="margin-top:24px;">
                <a href="<?= SITE_URL ?>/pages/ofertas.php" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-tags"></i> Ver todas las ofertas
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============ PRODUCTOS DESTACADOS ============ -->
<section class="section">
    <div class="container">
        <div class="section-header center reveal">
            <span class="section-eyebrow">Selección <?= SITE_NAME ?></span>
            <h2 class="section-title">Productos <span class="highlight">Destacados</span></h2>
            <p class="section-subtitle">Los favoritos de nuestros clientes, con la mejor calidad y precio.</p>
        </div>
        <div class="productos-grid">
            <?php foreach ($destacados as $p): ?>
                <?php include __DIR__ . '/includes/_producto_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:40px;">
            <a href="<?= SITE_URL ?>/pages/tienda.php" class="btn btn-primary btn-lg">
                Ver todos los productos <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============ OFERTAS PEQUEÑAS ============ -->
<section class="section" style="background:var(--gris-claro);">
    <div class="container">
        <div class="section-header center reveal">
            <span class="section-eyebrow">Ahorra más</span>
            <h2 class="section-title">Ofertas <span class="highlight">Imperdibles</span></h2>
            <p class="section-subtitle">Descuentos reales en productos que usas todos los días.</p>
        </div>
        <div class="productos-grid">
            <?php foreach ($ofertas as $p): ?>
                <?php include __DIR__ . '/includes/_producto_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ MÁS VENDIDOS ============ -->
<section class="section">
    <div class="container">
        <div class="section-header center reveal">
            <span class="section-eyebrow">Los preferidos</span>
            <h2 class="section-title">Más <span class="highlight">Vendidos</span></h2>
            <p class="section-subtitle">Lo que más compran nuestros clientes. Calidad comprobada.</p>
        </div>
        <div class="productos-grid">
            <?php foreach ($mas_vendidos as $p): ?>
                <?php include __DIR__ . '/includes/_producto_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CTA MAYORISTA ============ -->
<section class="section" style="padding:40px 0;">
    <div class="container">
        <div style="background:linear-gradient(135deg, var(--verde-oscuro), var(--verde-claro-2)); border-radius:var(--radius-xl); padding:50px; color:white; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:30px;">
            <div style="flex:1; min-width:280px;">
                <h2 style="color:white; font-size:30px; margin-bottom:10px;">¿Tienes una bodega o restaurante?</h2>
                <p style="opacity:0.95; font-size:16px;">Conoce nuestros precios especiales por mayor. Atención personalizada y entrega a domicilio.</p>
            </div>
            <a href="<?= SITE_URL ?>/pages/contacto.php" class="btn btn-yellow btn-lg">
                <i class="fa-solid fa-handshake"></i> Solicitar cotización
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Pasar SITE_URL a JS -->
<script>
    var SITE_URL_JS = '<?= SITE_URL ?>';
</script>
