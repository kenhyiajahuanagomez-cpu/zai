<?php
/**
 * BodegaViva - Autenticación de clientes
 * Funciones para registro, login, logout y gestión de sesión
 */

/**
 * Verifica si hay un cliente logueado
 */
function isCliente() {
    return isset($_SESSION['cliente_id']) && $_SESSION['cliente_id'] > 0;
}

/**
 * Obtiene el cliente logueado (array) o null
 */
function getCliente() {
    if (!isCliente()) return null;
    global $db;
    return $db->fetchOne(
        "SELECT id, nombre, celular, email, direccion, referencia, avatar, fecha_registro 
         FROM clientes WHERE id = ?",
        ['i', $_SESSION['cliente_id']]
    );
}

/**
 * Verifica login, redirige si no está autenticado
 */
function requireCliente() {
    if (!isCliente()) {
        flash('error', 'Debes iniciar sesión para acceder a esta página.');
        header('Location: ' . SITE_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        echo '<script>window.location.href="' . SITE_URL . '/pages/login.php";</script>';
        exit;
    }
}

/**
 * Registra un nuevo cliente
 * Devuelve ['success' => bool, 'errores' => [], 'cliente_id' => int]
 */
function registrarCliente($datos) {
    global $db, $conn;
    $errores = [];
    
    $nombre = trim($datos['nombre'] ?? '');
    $celular = trim($datos['celular'] ?? '');
    $email = trim($datos['email'] ?? '');
    $password = $datos['password'] ?? '';
    $direccion = trim($datos['direccion'] ?? '');
    
    // Validaciones
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
    if (empty($celular)) $errores[] = 'El celular es obligatorio';
    elseif (!preg_match('/^9\d{8}$/', $celular)) $errores[] = 'El celular debe tener 9 dígitos y empezar con 9';
    if (empty($email)) $errores[] = 'El email es obligatorio';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido';
    if (empty($password)) $errores[] = 'La contraseña es obligatoria';
    elseif (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    
    // Verificar email único
    if (empty($errores)) {
        $existe = $db->fetchOne("SELECT id FROM clientes WHERE email = ?", ['s', $email]);
        if ($existe) $errores[] = 'Ya existe una cuenta con este email';
    }
    // Verificar celular único
    if (empty($errores)) {
        $existe = $db->fetchOne("SELECT id FROM clientes WHERE celular = ?", ['s', $celular]);
        if ($existe) $errores[] = 'Ya existe una cuenta con este celular';
    }
    
    if (!empty($errores)) {
        return ['success' => false, 'errores' => $errores];
    }
    
    // Hashear contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar
    $cliente_id = $db->insert(
        "INSERT INTO clientes (nombre, celular, email, password, direccion) VALUES (?, ?, ?, ?, ?)",
        ['sssss', $nombre, $celular, $email, $hash, $direccion]
    );
    
    return ['success' => true, 'cliente_id' => $cliente_id, 'errores' => []];
}

/**
 * Inicia sesión de cliente
 * Devuelve true si fue exitoso
 */
function loginCliente($email, $password, $recordar = false) {
    global $db;
    $cliente = $db->fetchOne("SELECT * FROM clientes WHERE email = ?", ['s', $email]);
    if (!$cliente) return false;
    if (!$cliente['password']) return false; // No tiene contraseña (cliente solo por pedido)
    
    if (password_verify($password, $cliente['password'])) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'];
        $_SESSION['cliente_email'] = $cliente['email'];
        
        // Actualizar último login
        $db->execute("UPDATE clientes SET ultimo_login = NOW() WHERE id = ?", ['i', $cliente['id']]);
        
        // Cookie "recordarme" por 30 días
        if ($recordar) {
            $token = bin2hex(random_bytes(32));
            setcookie('bodegaviva_remember', $cliente['id'] . ':' . $token, time() + 30*24*60*60, '/');
            // En producción, guardar el token en BD para verificar
        }
        return true;
    }
    return false;
}

/**
 * Cierra sesión de cliente
 */
function logoutCliente() {
    unset($_SESSION['cliente_id']);
    unset($_SESSION['cliente_nombre']);
    unset($_SESSION['cliente_email']);
    if (isset($_COOKIE['bodegaviva_remember'])) {
        setcookie('bodegaviva_remember', '', time() - 3600, '/');
    }
    session_regenerate_id(true);
}

/**
 * Verifica si "recordarme" está activo
 */
function checkRememberCookie() {
    if (isCliente()) return;
    if (!isset($_COOKIE['bodegaviva_remember'])) return;
    
    list($cliente_id, $token) = explode(':', $_COOKIE['bodegaviva_remember'], 2);
    $cliente_id = (int)$cliente_id;
    if ($cliente_id <= 0) return;
    
    global $db;
    $cliente = $db->fetchOne("SELECT id, nombre, email FROM clientes WHERE id = ?", ['i', $cliente_id]);
    if ($cliente) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'];
        $_SESSION['cliente_email'] = $cliente['email'];
    }
}
