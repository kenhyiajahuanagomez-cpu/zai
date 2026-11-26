<?php
/**
 * BodegaViva - Cerrar sesión admin (limpia todo completamente)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Limpiar todas las variables de sesión del admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nombre']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_rol']);

// Si hay cookies de "recordarme", eliminarlas
if (isset($_COOKIE['bodegaviva_remember'])) {
    setcookie('bodegaviva_remember', '', time() - 3600, '/');
}

// Destruir la sesión completamente
session_unset();
session_destroy();

// Iniciar una nueva sesión vacía
session_start();
session_regenerate_id(true);

// Redirigir al login
header('Location: ' . SITE_URL . '/admin/index.php');
echo '<script>window.location.href="' . SITE_URL . '/admin/index.php";</script>';
exit;
