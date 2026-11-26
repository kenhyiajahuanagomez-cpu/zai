<?php
/**
 * Configuración principal del sistema BodegaViva
 * Proyecto: BodegaViva - Tienda de Abarrotes
 */

// ============ BUFFER DE SALIDA (DEBE ESTAR PRIMERO) ============
// Activa el buffer ANTES que cualquier otra cosa para evitar errores
// "headers already sent" al llamar header() o redirigir() después de HTML
if (!ob_get_level()) {
    ob_start();
}

// ============ CONFIGURACIÓN DE LA BASE DE DATOS ============
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'zai_tienda');
define('DB_CHARSET', 'utf8mb4');

// ============ CONFIGURACIÓN DEL SITIO ============
define('SITE_NAME', 'BodegaViva');
define('SITE_SLOGAN', 'Tu bodega siempre cerca');
define('SITE_EMAIL', 'contacto@bodegaviva.pe');
define('SITE_PREFIX', 'BV'); // Prefijo para IDs de pedidos

// ============ AUTO-LOGIN DEL ADMIN ============
// Si es true, accede directo al panel admin SIN pedir usuario ni contraseña
// Cambiar a false si vas a publicar tu web públicamente por seguridad
// IMPORTANTE: false = SIEMPRE pide usuario y contraseña (recomendado)
define('AUTO_LOGIN_ADMIN', false);

// ============ SITE_URL DINÁMICO ============
// Detecta automáticamente la URL (localhost o túnel público como ngrok/cloudflare)
// Esto permite que la web funcione tanto localmente como cuando se publica
if (!function_exists('detectarSiteUrl')) {
    function detectarSiteUrl() {
        // Si está definido en variable de entorno (para forzar una URL)
        if (getenv('SITE_URL_FORCE')) return getenv('SITE_URL_FORCE');
        // Si está en constante (definido manualmente en config)
        if (defined('SITE_URL_OVERRIDE') && SITE_URL_OVERRIDE) return SITE_URL_OVERRIDE;
        
        // Detectar protocolo (importante para ngrok/cloudflare que usan HTTPS)
        $es_https = false;
        // Caso 1: HTTPS directo en el servidor
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') $es_https = true;
        // Caso 2: Puerto 443
        if (($_SERVER['SERVER_PORT'] ?? '') == 443) $es_https = true;
        // Caso 3: Detrás de un proxy/túnel que envía X-Forwarded-Proto (ngrok, cloudflare, etc.)
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
            strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) $es_https = true;
        // Caso 4: Header X-Forwarded-SSL
        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') $es_https = true;
        // Caso 5: Header Front-End-Https (algunos proxies)
        if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === 'on') $es_https = true;
        
        $protocol = $es_https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Detectar el subdirectorio (por ejemplo /zai_tienda)
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        // Buscar la carpeta raíz del proyecto (donde está index.php)
        $carpeta = '';
        if (preg_match('#^/(.+?)/#', $script, $m)) {
            // Si la URL incluye una carpeta como /zai_tienda/...
            $posible = $m[1];
            // Verificar que no sea una carpeta de pages/ o admin/
            if (!in_array($posible, ['pages', 'admin', 'config', 'includes', 'css', 'js', 'img'])) {
                $carpeta = '/' . $posible;
            }
        }
        return $protocol . '://' . $host . $carpeta;
    }
}
define('SITE_URL', detectarSiteUrl());

// Para forzar una URL específica (ej. ngrok), descomenta y edita:
// define('SITE_URL_OVERRIDE', 'https://tu-tunel.ngrok.io/zai_tienda');

// ============ CONTACTO ============
define('WHATSAPP_NUMBER', '51916554080'); // 51 + 916554080 (Perú)
define('CONTACT_PHONE', '(051) 362-555');
define('STORE_ADDRESS', 'Jr. Lima 540, Centro Histórico, Puno');
define('STORE_HOURS', 'Lun - Dom: 7:00 am - 10:00 pm');

// ============ MONEDA ============
define('CURRENCY', 'S/');
define('CURRENCY_CODE', 'PEN');

// ============ CONFIGURACIÓN DE SESIÓN ============
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ ZONA HORARIA ============
date_default_timezone_set('America/Lima');

// ============ ERRORES (desactivar warnings que rompen headers) ============
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============ CONFIGURACIÓN WHATSAPP ============
define('WHATSAPP_API_URL', 'https://wa.me/');

// ============ ESTADOS DE PEDIDO ============
$ESTADOS_PEDIDO = [
    'pendiente'   => ['label' => 'Pendiente', 'color' => 'warning'],
    'procesando'  => ['label' => 'Procesando', 'color' => 'info'],
    'enviado'     => ['label' => 'Enviado', 'color' => 'primary'],
    'entregado'   => ['label' => 'Entregado', 'color' => 'success'],
    'cancelado'   => ['label' => 'Cancelado', 'color' => 'danger'],
];

// ============ MÉTODOS DE PAGO ============
$METODOS_PAGO = [
    'efectivo'     => 'Efectivo',
    'yape'         => 'Yape',
    'plin'         => 'Plin',
    'transferencia'=> 'Transferencia Bancaria',
];
