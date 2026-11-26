<?php
/**
 * BodegaViva - Página 404 personalizada
 */
http_response_code(404);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_activa = '';
$titulo = 'Página no encontrada';
$css_extra = [];

include __DIR__ . '/includes/header.php';
?>

<style>
.error-404 {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.error-404::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(123,201,80,0.15), transparent 70%);
    border-radius: 50%;
}
.error-404::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,210,63,0.15), transparent 70%);
    border-radius: 50%;
}
.error-404-content {
    position: relative;
    z-index: 1;
    max-width: 600px;
}
.error-number {
    font-family: 'Quicksand', sans-serif;
    font-size: 140px;
    font-weight: 800;
    line-height: 1;
    background: linear-gradient(135deg, #0E5A3C, #7BC950);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
    animation: floatNum 3s ease-in-out infinite;
}
@keyframes floatNum {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.error-icon {
    font-size: 60px;
    color: var(--amarillo);
    margin-bottom: 20px;
    animation: bounce 1.5s ease infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}
.error-404 h1 {
    font-size: 32px;
    color: var(--verde-oscuro);
    margin-bottom: 12px;
}
.error-404 p {
    color: var(--gris-texto);
    font-size: 16px;
    margin-bottom: 30px;
    line-height: 1.6;
}
.error-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.error-suggestions {
    margin-top: 40px;
    padding: 24px;
    background: var(--blanco);
    border-radius: var(--radius-lg);
    box-shadow: var(--sombra-1);
}
.error-suggestions h3 {
    font-size: 16px;
    color: var(--verde-oscuro);
    margin-bottom: 14px;
}
.error-suggestions ul {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    list-style: none;
}
.error-suggestions li a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: var(--gris-claro);
    border-radius: 20px;
    font-size: 13px;
    color: var(--texto-2);
    text-decoration: none;
    transition: all 0.2s;
}
.error-suggestions li a:hover {
    background: var(--verde-claro);
    color: var(--blanco);
    transform: translateY(-2px);
}
</style>

<section class="error-404">
    <div class="error-404-content">
        <div class="error-icon"><i class="fa-solid fa-face-frown-open"></i></div>
        <div class="error-number">404</div>
        <h1>¡Oops! Página no encontrada</h1>
        <p>Parece que la página que buscas no existe, fue movida o el enlace está roto. <br>Pero no te preocupes, ¡podemos ayudarte a encontrar lo que necesitas!</p>
        
        <div class="error-actions">
            <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-house"></i> Ir al inicio
            </a>
            <a href="<?= SITE_URL ?>/pages/tienda.php" class="btn btn-yellow btn-lg">
                <i class="fa-solid fa-shop"></i> Ver tienda
            </a>
        </div>
        
        <div class="error-suggestions">
            <h3><i class="fa-solid fa-lightbulb"></i> Enlaces populares</h3>
            <ul>
                <li><a href="<?= SITE_URL ?>/pages/tienda.php"><i class="fa-solid fa-shop"></i> Tienda</a></li>
                <li><a href="<?= SITE_URL ?>/pages/ofertas.php"><i class="fa-solid fa-tags"></i> Ofertas</a></li>
                <li><a href="<?= SITE_URL ?>/pages/carrito.php"><i class="fa-solid fa-cart-shopping"></i> Carrito</a></li>
                <li><a href="<?= SITE_URL ?>/pages/login.php"><i class="fa-solid fa-user"></i> Mi cuenta</a></li>
                <li><a href="<?= SITE_URL ?>/pages/nosotros.php"><i class="fa-solid fa-store"></i> Nosotros</a></li>
                <li><a href="<?= SITE_URL ?>/pages/contacto.php"><i class="fa-solid fa-headset"></i> Contacto</a></li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
