<?php
/**
 * Funciones auxiliares y utilidades del sistema BodegaViva
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

// Verificar cookie "recordarme" al cargar functions.php
checkRememberCookie();

/**
 * Limpia y sanitiza una cadena de texto
 */
function limpiar($str) {
    global $conn;
    $str = trim($str);
    $str = stripslashes($str);
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($str);
}

/**
 * Formatea un número como precio en soles (S/)
 */
function formatPrecio($valor) {
    return CURRENCY . ' ' . number_format((float)$valor, 2, '.', ',');
}

/**
 * Obtiene el total de items en el carrito (cantidad)
 */
function carritoCantidad() {
    if (!isset($_SESSION['carrito'])) return 0;
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

/**
 * Obtiene el total en soles del carrito
 */
function carritoTotal() {
    if (!isset($_SESSION['carrito'])) return 0;
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total;
}

/**
 * Genera un enlace de WhatsApp con un mensaje precargado
 */
function whatsappLink($mensaje) {
    return WHATSAPP_API_URL . WHATSAPP_NUMBER . '?text=' . urlencode($mensaje);
}

/**
 * Calcula el porcentaje de descuento entre dos precios
 */
function calcularDescuento($precio_actual, $precio_anterior) {
    if (!$precio_anterior || $precio_anterior <= 0) return 0;
    $descuento = (($precio_anterior - $precio_actual) / $precio_anterior) * 100;
    return round($descuento);
}

/**
 * Acorta un texto a un máximo de caracteres
 */
function acortar($texto, $max = 80) {
    if (strlen($texto) <= $max) return $texto;
    return substr($texto, 0, $max) . '...';
}

/**
 * Redirige a una URL (con fallback a JavaScript si los headers ya fueron enviados)
 */
function redirigir($url) {
    // Limpiar cualquier buffer de salida activo
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    // Intentar redirección con header() si aún no se enviaron headers
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    // Fallback: redirección con JavaScript + meta refresh
    echo '<script>window.location.href=' . json_encode($url) . ';</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
    exit;
}

/**
 * Genera un token CSRF
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica el token CSRF
 */
function verificarCsrf($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('Token CSRF inválido.');
    }
}

/**
 * Sube y guarda una imagen
 * Devuelve la ruta absoluta (con SITE_URL) del archivo o false si falla
 */
function subirImagen($archivo, $carpeta = 'productos') {
    if (!isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $permitidas)) return false;

    $nombre = uniqid('img_') . '.' . $ext;
    $ruta_absoluta = __DIR__ . '/../img/' . $carpeta . '/';
    if (!is_dir($ruta_absoluta)) @mkdir($ruta_absoluta, 0755, true);

    if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta . $nombre)) {
        // Devolver ruta absoluta para que funcione desde cualquier página
        return SITE_URL . '/img/' . $carpeta . '/' . $nombre;
    }
    return false;
}

/**
 * Normaliza una URL de imagen para mostrarla correctamente
 * - Si es relativa (img/productos/...), la convierte a absoluta con SITE_URL
 * - Si ya es absoluta (http...) o data URI, la deja igual
 */
function normalizarImagen($url) {
    if (empty($url)) return '';
    if (strpos($url, 'http') === 0 || strpos($url, 'data:') === 0) return $url;
    // Si ya empieza con SITE_URL, dejar igual
    if (strpos($url, SITE_URL) === 0) return $url;
    // Si empieza con /, es ruta absoluta del servidor
    if (strpos($url, '/') === 0) return SITE_URL . $url;
    // Si es relativa (img/...), prepend SITE_URL/
    return SITE_URL . '/' . ltrim($url, '/');
}

/**
 * Muestra una notificación flash
 */
function flash($tipo, $mensaje) {
    if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Obtiene y limpia las notificaciones flash
 */
function getFlash() {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Obtiene todas las categorías
 */
function obtenerCategorias() {
    global $db;
    return $db->fetchAll("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre");
}

/**
 * Obtiene productos destacados
 */
function obtenerDestacados($limite = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.destacado = 1 AND p.estado = 1 
         ORDER BY p.vendidos DESC LIMIT ?",
        ['i', $limite]
    );
}

/**
 * Obtiene productos en oferta
 */
function obtenerOfertas($limite = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.en_oferta = 1 AND p.estado = 1 
         ORDER BY p.vendidos DESC LIMIT ?",
        ['i', $limite]
    );
}

/**
 * Obtiene productos más vendidos
 */
function obtenerMasVendidos($limite = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.mas_vendido = 1 AND p.estado = 1 
         ORDER BY p.vendidos DESC LIMIT ?",
        ['i', $limite]
    );
}

/**
 * Obtiene productos por categoría
 */
function obtenerProductosPorCategoria($categoria_id, $limite = 100) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.categoria_id = ? AND p.estado = 1 
         ORDER BY p.nombre LIMIT ?",
        ['ii', $categoria_id, $limite]
    );
}

/**
 * Busca productos por nombre o marca
 */
function buscarProductos($q) {
    global $db;
    $like = '%' . $q . '%';
    return $db->fetchAll(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE (p.nombre LIKE ? OR p.marca LIKE ? OR p.descripcion LIKE ?) 
         AND p.estado = 1 
         ORDER BY p.vendidos DESC",
        ['sss', $like, $like, $like]
    );
}

/**
 * Obtiene un producto por su ID
 */
function obtenerProducto($id) {
    global $db;
    return $db->fetchOne(
        "SELECT p.*, c.nombre AS categoria_nombre 
         FROM productos p 
         INNER JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.id = ?",
        ['i', $id]
    );
}

/**
 * Verifica si el admin está logueado de forma segura
 * Verifica que exista la sesión Y que el usuario siga activo en BD
 */
function isAdmin() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] <= 0) return false;
    // Verificación extra: comprobar que el usuario sigue activo en BD
    global $db;
    if (!isset($db)) return true; // Si no hay BD, confiar en sesión
    $user = $db->fetchOne(
        "SELECT id, estado FROM usuarios WHERE id = ? AND estado = 1",
        ['i', $_SESSION['admin_id']]
    );
    return $user ? true : false;
}

/**
 * Verifica login admin, redirige al login si no está autenticado
 * USA SIEMPRE el login del admin (no auto-login)
 */
function requireAdmin() {
    if (!isAdmin()) {
        // Limpiar sesión inválida
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_nombre']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_rol']);
        // Redirigir al login
        header('Location: ' . SITE_URL . '/admin/index.php');
        echo '<script>window.location.href="' . SITE_URL . '/admin/index.php";</script>';
        exit;
    }
}

/**
 * Genera un slug de una cadena
 */
function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}
