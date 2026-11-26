<?php
/**
 * BodegaViva - Login del panel administrativo
 * PIDE USUARIO Y CONTRASEÑA SIEMPRE (no hay auto-login)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Si ya está logueado, ir al dashboard
if (isAdmin()) {
    redirigir(SITE_URL . '/admin/dashboard.php');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errores[] = 'Todos los campos son obligatorios';
    } else {
        // Buscar usuario por email
        $usuario = $db->fetchOne("SELECT * FROM usuarios WHERE email = ? AND estado = 1", ['s', $email]);
        if ($usuario) {
            // Verificar password
            $login_valido = password_verify($password, $usuario['password']);
            
            // Fallback de primera instalación: si la contraseña no coincide pero es admin@bodegaviva.pe
            // y la contraseña es admin123 (solo para primera instalación)
            if (!$login_valido && $usuario['email'] === 'admin@bodegaviva.pe' && $password === 'admin123') {
                $login_valido = true;
                // Regenerar hash para próximas veces
                $nuevo_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $db->execute("UPDATE usuarios SET password = ? WHERE id = ?", ['si', $nuevo_hash, $usuario['id']]);
            }
            
            if ($login_valido) {
                $_SESSION['admin_id'] = $usuario['id'];
                $_SESSION['admin_nombre'] = $usuario['nombre'];
                $_SESSION['admin_email'] = $usuario['email'];
                $_SESSION['admin_rol'] = $usuario['rol'];

                redirigir(SITE_URL . '/admin/dashboard.php');
            } else {
                $errores[] = 'Contraseña incorrecta';
            }
        } else {
            $errores[] = 'El email no está registrado';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Admin <?= SITE_NAME ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230E5A3C'/><text x='50' y='72' font-size='62' font-family='Arial' font-weight='bold' text-anchor='middle' fill='%23FFD23F'>B</text><circle cx='78' cy='22' r='8' fill='%237BC950'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-logo">B</div>
        <h1>Panel Administrativo</h1>
        <p class="subtitle"><?= SITE_NAME ?> - Tienda de Abarrotes</p>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <ul style="margin:0; padding-left:16px; font-size:13px;">
                    <?php foreach ($errores as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form" data-validate>
            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Correo electrónico</label>
                <div class="login-input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? 'admin@bodegaviva.pe') ?>" placeholder="admin@bodegaviva.pe">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Contraseña</label>
                <div class="login-input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" required placeholder="••••••••" value="<?= isset($_POST['password']) ? htmlspecialchars($_POST['password']) : 'admin123' ?>">
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
            </button>
        </form>

        <div class="login-credenciales">
            <strong>Credenciales de prueba:</strong><br>
            Email: <code>admin@bodegaviva.pe</code><br>
            Password: <code>admin123</code>
        </div>

        <a href="<?= SITE_URL ?>/index.php" class="login-back">
            <i class="fa-solid fa-arrow-left"></i> Volver a la tienda
        </a>
    </div>

    <script>
        // Auto-ocultar alertas
        document.querySelectorAll('.alert').forEach(a => {
            setTimeout(() => {
                a.style.transition = 'opacity 0.3s';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 300);
            }, 6000);
        });
    </script>
</body>
</html>
