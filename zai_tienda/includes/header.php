<?php
/**
 * Header y navbar principal del sitio BodegaViva
 * Variables disponibles:
 *   $pagina_activa: nombre de la página actual para resaltar el menú
 */
// Buffer de salida como red de seguridad (en caso de BOM en config.php)
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($pagina_activa)) $pagina_activa = '';
$carrito_cantidad = carritoCantidad();
$categorias = obtenerCategorias();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= SITE_NAME ?> - Tienda de abarrotes. <?= SITE_SLOGAN ?>. Productos frescos, entrega rápida y los mejores precios en Perú.">
    <title><?= isset($titulo) ? $titulo . ' | ' : '' ?><?= SITE_NAME ?> - Tienda de Abarrotes</title>

    <!-- Favicon SVG inline -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230E5A3C'/><text x='50' y='72' font-size='62' font-family='Arial' font-weight='bold' text-anchor='middle' fill='%23FFD23F'>B</text><circle cx='78' cy='22' r='8' fill='%237BC950'/></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <?php if (isset($css_extra)): ?>
        <?php foreach ($css_extra as $css): ?>
            <link rel="stylesheet" href="<?= SITE_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<!-- ============ BARRA SUPERIOR ============ -->
<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-left">
            <span><i class="fa-solid fa-location-dot"></i> <?= STORE_ADDRESS ?></span>
        </div>
        <div class="topbar-right">
            <span><i class="fa-solid fa-clock"></i> <?= STORE_HOURS ?></span>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" class="topbar-wa">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>
</div>

<!-- ============ HEADER / NAVBAR ============ -->
<header class="header" id="header">
    <div class="container header-inner">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/index.php" class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="gradLogo" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#0E5A3C"/>
                            <stop offset="100%" style="stop-color:#5DA838"/>
                        </linearGradient>
                    </defs>
                    <rect width="100" height="100" rx="24" fill="url(#gradLogo)"/>
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

        <!-- Buscador -->
        <form action="<?= SITE_URL ?>/pages/tienda.php" method="GET" class="search-bar" role="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Buscar productos, marcas..." 
                   value="<?= $_GET['q'] ?? '' ?>" autocomplete="off">
            <button type="submit">Buscar</button>
        </form>

        <!-- Acciones (carrito siempre visible en desktop) -->
        <div class="header-actions">
            <?php if (isCliente()): ?>
                <a href="<?= SITE_URL ?>/pages/perfil.php" class="icon-btn <?= $pagina_activa === 'perfil' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user"></i>
                    <span>Mi Cuenta</span>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/pages/login.php" class="icon-btn <?= $pagina_activa === 'login' ? 'active' : '' ?>">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Ingresar</span>
                </a>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/pages/carrito.php" class="icon-btn cart-btn <?= $pagina_activa === 'carrito' ? 'active' : '' ?>">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Carrito</span>
                <?php if ($carrito_cantidad > 0): ?>
                    <span class="cart-count" id="cartCount"><?= $carrito_cantidad ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Botón menú móvil (muestra todos los botones) -->
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Navbar de navegación -->
    <nav class="navbar" id="navbar">
        <div class="container navbar-inner">
            <ul class="nav-menu">
                <li><a href="<?= SITE_URL ?>/index.php" class="<?= $pagina_activa === 'inicio' ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i> Inicio
                </a></li>
                <li><a href="<?= SITE_URL ?>/pages/tienda.php" class="<?= $pagina_activa === 'tienda' ? 'active' : '' ?>">
                    <i class="fa-solid fa-shop"></i> Tienda
                </a></li>
                <li class="dropdown">
                    <a href="#" class="<?= $pagina_activa === 'categorias' ? 'active' : '' ?>">
                        <i class="fa-solid fa-layer-group"></i> Categorías <i class="fa-solid fa-chevron-down small"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categorias as $cat): ?>
                            <li>
                                <a href="<?= SITE_URL ?>/pages/categoria.php?id=<?= $cat['id'] ?>">
                                    <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($cat['nombre']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="<?= SITE_URL ?>/pages/ofertas.php" class="<?= $pagina_activa === 'ofertas' ? 'active' : '' ?>">
                    <i class="fa-solid fa-tags"></i> Ofertas
                </a></li>
                <li><a href="<?= SITE_URL ?>/pages/nosotros.php" class="<?= $pagina_activa === 'nosotros' ? 'active' : '' ?>">
                    <i class="fa-solid fa-store"></i> Nosotros
                </a></li>
                <li><a href="<?= SITE_URL ?>/pages/contacto.php" class="<?= $pagina_activa === 'contacto' ? 'active' : '' ?>">
                    <i class="fa-solid fa-phone"></i> Contacto
                </a></li>
            </ul>
            <a href="<?= SITE_URL ?>/admin/index.php" class="nav-admin">
                <i class="fa-solid fa-lock"></i> Admin
            </a>
        </div>
    </nav>
</header>

<!-- ============ NOTIFICACIONES FLASH ============ -->
<?php if (!empty($flash)): ?>
<div class="flash-container">
    <?php foreach ($flash as $f): ?>
        <div class="flash flash-<?= $f['tipo'] ?>">
            <i class="fa-solid <?= $f['tipo'] === 'success' ? 'fa-circle-check' : ($f['tipo'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-info') ?>"></i>
            <span><?= htmlspecialchars($f['mensaje']) ?></span>
            <button class="flash-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ============ CONTENIDO PRINCIPAL ============ -->
<main class="main-content">

<!-- Pasar SITE_URL a JS para todos los scripts -->
<script>var SITE_URL_JS = '<?= SITE_URL ?>';</script>
