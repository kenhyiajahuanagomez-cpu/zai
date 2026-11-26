<?php
/**
 * Footer del sitio ZAI
 */
$categorias_footer = obtenerCategorias();
?>
</main>

<!-- ============ NEWSLETTER ============ -->
<section class="newsletter">
    <div class="container">
        <div class="newsletter-box">
            <div class="newsletter-text">
                <h2><i class="fa-solid fa-envelope-open-text"></i> Recibe ofertas exclusivas</h2>
                <p>Suscríbete y entérate primero de nuestros descuentos y productos nuevos.</p>
            </div>
            <form class="newsletter-form" onsubmit="return false;">
                <input type="email" placeholder="Tu correo electrónico" required>
                <button type="submit">Suscribirme</button>
            </form>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Columna 1: Marca -->
            <div class="footer-col">
                <a href="<?= SITE_URL ?>/index.php" class="logo footer-logo">
                    <div class="logo-icon">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="gradLogoF" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#0E5A3C"/>
                                    <stop offset="100%" style="stop-color:#5DA838"/>
                                </linearGradient>
                            </defs>
                            <rect width="100" height="100" rx="24" fill="url(#gradLogoF)"/>
                            <path d="M32 26 L68 26 Q78 26 78 36 L78 44 Q78 52 70 54 Q78 56 78 64 L78 72 Q78 78 68 78 L32 78 Z" fill="#FFD23F"/>
                            <text x="50" y="62" font-family="Arial" font-weight="900" font-size="42" text-anchor="middle" fill="#0E5A3C">B</text>
                            <circle cx="76" cy="24" r="7" fill="#7BC950"/>
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="logo-name"><?= SITE_NAME ?></span>
                        <span class="logo-slogan">Tienda de Abarrotes</span>
                    </div>
                </a>
                <p class="footer-desc"><?= SITE_SLOGAN ?>. Tu bodega de confianza en línea, con productos frescos y entregas rápidas en todo Lima.</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Columna 2: Enlaces rápidos -->
            <div class="footer-col">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Inicio</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/tienda.php">Tienda</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/ofertas.php">Ofertas</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/nosotros.php">Nosotros</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/contacto.php">Contacto</a></li>
                </ul>
            </div>

            <!-- Columna 3: Categorías -->
            <div class="footer-col">
                <h4>Categorías</h4>
                <ul>
                    <?php foreach ($categorias_footer as $cat): ?>
                        <li><a href="<?= SITE_URL ?>/pages/categoria.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Columna 4: Contacto -->
            <div class="footer-col">
                <h4>Contacto</h4>
                <ul class="contact-list">
                    <li><i class="fa-solid fa-location-dot"></i> <?= STORE_ADDRESS ?></li>
                    <li><i class="fa-solid fa-phone"></i> <?= CONTACT_PHONE ?></li>
                    <li><i class="fa-brands fa-whatsapp"></i> +<?= WHATSAPP_NUMBER ?></li>
                    <li><i class="fa-solid fa-envelope"></i> <?= SITE_EMAIL ?></li>
                    <li><i class="fa-solid fa-clock"></i> <?= STORE_HOURS ?></li>
                </ul>
            </div>

            <!-- Columna 5: Métodos de pago -->
            <div class="footer-col">
                <h4>Métodos de pago</h4>
                <div class="payment-methods">
                    <span class="pay-badge efectivo">Efectivo</span>
                    <span class="pay-badge yape">Yape</span>
                    <span class="pay-badge plin">Plin</span>
                    <span class="pay-badge transferencia">Transferencia</span>
                </div>
                <h4 style="margin-top:1.5rem;">Certificado</h4>
                <div class="cert-badges">
                    <span class="cert-badge"><i class="fa-solid fa-shield-halved"></i> Sitio Seguro</span>
                    <span class="cert-badge"><i class="fa-solid fa-truck-fast"></i> Envío Rápido</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= SITE_NAME ?> - Tienda de Abarrotes. Todos los derechos reservados.</p>
            <p>Hecho con <i class="fa-solid fa-heart"></i> en Lima, Perú</p>
        </div>
    </div>
</footer>

<!-- ============ BOTÓN FLOTANTE WHATSAPP ============ -->
<a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode('Hola ' . SITE_NAME . ', quisiera más información sobre sus productos.') ?>" 
   class="whatsapp-float" target="_blank" aria-label="WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<!-- ============ BOTÓN SCROLL TO TOP ============ -->
<button class="scroll-top" id="scrollTop" aria-label="Volver arriba">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- ============ BARRA INFERIOR MÓVIL (bottom bar) ============ -->
<nav class="mobile-bottom-bar">
    <div class="mobile-bottom-bar-inner">
        <a href="<?= SITE_URL ?>/index.php" class="<?= $pagina_activa === 'inicio' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>
        <a href="<?= SITE_URL ?>/pages/tienda.php" class="<?= $pagina_activa === 'tienda' ? 'active' : '' ?>">
            <i class="fa-solid fa-shop"></i>
            <span>Tienda</span>
        </a>
        <a href="<?= SITE_URL ?>/pages/ofertas.php" class="<?= $pagina_activa === 'ofertas' ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            <span>Ofertas</span>
        </a>
        <a href="<?= SITE_URL ?>/pages/carrito.php" class="<?= $pagina_activa === 'carrito' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Carrito</span>
            <?php if ($carrito_cantidad > 0): ?>
                <span class="cart-count"><?= $carrito_cantidad ?></span>
            <?php endif; ?>
        </a>
        <?php if (isCliente()): ?>
            <a href="<?= SITE_URL ?>/pages/perfil.php" class="<?= $pagina_activa === 'perfil' ? 'active' : '' ?>">
                <i class="fa-solid fa-user"></i>
                <span>Perfil</span>
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/pages/login.php" class="<?= $pagina_activa === 'login' ? 'active' : '' ?>">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Ingresar</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- ============ NOTIFICACIÓN TOAST ============ -->
<div class="toast-container" id="toastContainer"></div>

<!-- ============ SCRIPTS ============ -->
<script src="<?= SITE_URL ?>/js/main.js"></script>
<?php if (isset($js_extra)): ?>
    <?php foreach ($js_extra as $js): ?>
        <script src="<?= SITE_URL ?>/js/<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
