<?php
/**
 * BodegaViva - Generador de hash de contraseña
 * USO:
 * 1. Accede a http://localhost/zai_tienda/gen_password.php
 * 2. Escribe tu nueva contraseña
 * 3. Genera el hash
 * 4. Ejecuta el SQL en phpMyAdmin
 * 5. ¡ELIMINA este archivo después! (importante por seguridad)
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$resultado = '';
$nueva_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_password = $_POST['password'] ?? '';
    if (strlen($nueva_password) < 6) {
        $resultado = '<div class="alert alert-danger">La contraseña debe tener al menos 6 caracteres.</div>';
    } else {
        $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $resultado = '
        <div class="alert alert-success">
            <strong>¡Hash generado!</strong> Copia este SQL y ejecútalo en phpMyAdmin:
            <pre style="background:#0E5A3C; color:#FFD23F; padding:14px; border-radius:8px; margin-top:10px; overflow-x:auto; font-size:13px;">
UPDATE usuarios SET password = \'' . $hash . '\' WHERE email = \'admin@bodegaviva.pe\';
            </pre>
            <p style="margin-top:10px; color:#E53935;">
                <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (gen_password.php) después de usarlo.
            </p>
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Hash de Password | BodegaViva</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body {
            min-height:100vh; background:linear-gradient(135deg,#0E5A3C,#5DA838);
            display:flex; align-items:center; justify-content:center; padding:20px;
        }
        .card {
            background:white; max-width:560px; width:100%; padding:36px;
            border-radius:20px; box-shadow:0 30px 80px rgba(0,0,0,0.3);
        }
        h1 { color:#0E5A3C; font-size:24px; margin-bottom:6px; }
        p.subtitle { color:#6B7568; font-size:14px; margin-bottom:24px; }
        label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; }
        input[type="password"] {
            width:100%; padding:12px 14px; border:1px solid #D5D9D0;
            border-radius:10px; font-size:14px; outline:none; margin-bottom:16px;
        }
        input[type="password"]:focus { border-color:#7BC950; box-shadow:0 0 0 3px rgba(123,201,80,0.15); }
        button {
            background:#0E5A3C; color:white; padding:12px 24px; border:none;
            border-radius:10px; font-weight:600; cursor:pointer; font-size:14px;
        }
        button:hover { background:#5DA838; }
        .alert { padding:14px 18px; border-radius:10px; margin-top:20px; font-size:13px; }
        .alert-success { background:rgba(123,201,80,0.12); border-left:4px solid #7BC950; }
        .alert-danger { background:rgba(229,57,53,0.1); border-left:4px solid #E53935; color:#E53935; }
        .alert-warn { background:rgba(255,210,63,0.2); border-left:4px solid #FFD23F; margin-top:20px; }
        pre { white-space:pre-wrap; word-wrap:break-word; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 Generar Hash de Contraseña</h1>
        <p class="subtitle">Para cambiar la contraseña del administrador de forma segura.</p>
        
        <div class="alert-warn">
            <strong>⚠️ Advertencia:</strong> Elimina este archivo después de usarlo.<br>
            Ruta: <code>C:\xampp\htdocs\zai_tienda\gen_password.php</code>
        </div>
        
        <form method="POST" style="margin-top:20px;">
            <label>Nueva contraseña del admin (mínimo 6 caracteres)</label>
            <input type="password" name="password" required value="<?= htmlspecialchars($nueva_password) ?>" 
                   placeholder="Ej. MiNuevaClave2026" minlength="6">
            <button type="submit">🔐 Generar hash</button>
        </form>
        
        <?= $resultado ?>
        
        <p style="margin-top:30px; padding-top:20px; border-top:1px solid #ECEEE9; font-size:12px; color:#6B7568;">
            <strong>Pasos:</strong><br>
            1. Escribe tu nueva contraseña arriba y genera el hash<br>
            2. Copia el SQL generado<br>
            3. Ve a phpMyAdmin → base de datos <code>zai_tienda</code> → pestaña SQL<br>
            4. Pega y ejecuta el SQL<br>
            5. Inicia sesión en el admin con la nueva contraseña<br>
            6. <strong>¡Elimina este archivo!</strong>
        </p>
    </div>
</body>
</html>
