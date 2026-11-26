<?php
/**
 * Header del panel administrativo BodegaViva
 * Requiere sesión de admin activa - SIEMPRE pide login
 */
// Buffer de salida como red de seguridad (en caso de BOM en config.php)
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificación estricta: SIEMPRE pide login si no hay sesión válida
requireAdmin();

$pagina_activa = $pagina_activa ?? '';
$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador';
$pagina_titulo = $pagina_titulo ?? 'Panel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pagina_titulo ?> | Admin <?= SITE_NAME ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230E5A3C'/><text x='50' y='72' font-size='62' font-family='Arial' font-weight='bold' text-anchor='middle' fill='%23FFD23F'>B</text><circle cx='78' cy='22' r='8' fill='%237BC950'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
    <!-- Chart.js cargado en head para que esté disponible antes de los scripts inline -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php if (isset($css_extra)): ?>
        <?php foreach ($css_extra as $css): ?>
            <link rel="stylesheet" href="<?= SITE_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="sidebar-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="gradLogoA" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#FFD23F"/>
                                <stop offset="100%" style="stop-color:#E8B925"/>
                            </linearGradient>
                        </defs>
                        <rect width="100" height="100" rx="24" fill="url(#gradLogoA)"/>
                        <path d="M32 26 L68 26 Q78 26 78 36 L78 44 Q78 52 70 54 Q78 56 78 64 L78 72 Q78 78 68 78 L32 78 Z" fill="#0E5A3C"/>
                        <text x="50" y="62" font-family="Arial" font-weight="900" font-size="42" text-anchor="middle" fill="#FFD23F">B</text>
                        <circle cx="76" cy="24" r="7" fill="#7BC950"/>
                    </svg>
                </div>
                <div>
                    <span class="logo-name"><?= SITE_NAME ?></span>
                    <span class="logo-slogan">Panel Admin</span>
                </div>
            </a>
            <button class="sidebar-close" id="sidebarClose" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="<?= $pagina_activa === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/productos.php" class="<?= $pagina_activa === 'productos' ? 'active' : '' ?>">
                <i class="fa-solid fa-box"></i> <span>Productos</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/categorias.php" class="<?= $pagina_activa === 'categorias' ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> <span>Categorías</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/inventario.php" class="<?= $pagina_activa === 'inventario' ? 'active' : '' ?>">
                <i class="fa-solid fa-warehouse"></i> <span>Inventario</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pedidos.php" class="<?= $pagina_activa === 'pedidos' ? 'active' : '' ?>">
                <i class="fa-solid fa-receipt"></i> <span>Pedidos</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/clientes.php" class="<?= $pagina_activa === 'clientes' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> <span>Clientes</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/ventas.php" class="<?= $pagina_activa === 'ventas' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i> <span>Ventas</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/reportes.php" class="<?= $pagina_activa === 'reportes' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice"></i> <span>Reportes</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= SITE_URL ?>/index.php" target="_blank" class="sidebar-link-out">
                <i class="fa-solid fa-up-right-from-square"></i> Ver tienda
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="admin-title"><?= $pagina_titulo ?></h1>
            <div class="admin-user">
                <span class="admin-date"><?= date('d/m/Y') ?></span>
                <div class="admin-avatar">
                    <span><?= strtoupper(substr($admin_nombre, 0, 1)) ?></span>
                </div>
                <span class="admin-name"><?= htmlspecialchars($admin_nombre) ?></span>
            </div>
        </header>

        <main class="admin-content">
