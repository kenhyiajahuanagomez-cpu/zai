<?php
/**
 * BodegaViva Admin - Detalle de pedido
 */
// Buffer al inicio, antes que cualquier salida
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

// Procesar cambio de estado ANTES del header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $nuevo_estado = $_POST['estado'];
    $db->execute("UPDATE pedidos SET estado = ? WHERE id = ?", ['si', $nuevo_estado, $id]);
    flash('success', 'Estado del pedido actualizado.');
    header('Location: ' . SITE_URL . '/admin/pedido_detalle.php?id=' . $id);
    echo '<script>window.location.href="' . SITE_URL . '/admin/pedido_detalle.php?id=' . $id . '";</script>';
    exit;
}

$pedido = $db->fetchOne("SELECT * FROM pedidos WHERE id = ?", ['i', $id]);
if (!$pedido) {
    flash('error', 'Pedido no encontrado.');
    header('Location: ' . SITE_URL . '/admin/pedidos.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/pedidos.php";</script>';
    exit;
}

$detalles = $db->fetchAll("SELECT dp.*, p.imagen FROM detalle_pedidos dp LEFT JOIN productos p ON dp.producto_id = p.id WHERE dp.pedido_id = ?", ['i', $id]);

$pagina_activa = 'pedidos';
$pagina_titulo = 'Detalle del Pedido';
require_once __DIR__ . '/../includes/header_admin.php';
?>

<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : 'info' ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<div class="filter-bar">
    <div class="left">
        <a href="<?= SITE_URL ?>/admin/pedidos.php" class="btn-admin btn-admin-outline"><i class="fa-solid fa-arrow-left"></i> Volver a pedidos</a>
    </div>
    <div class="right">
        <a href="https://wa.me/51<?= preg_replace('/\D/', '', $pedido['celular']) ?>?text=<?= urlencode('Hola ' . $pedido['nombre_cliente'] . ', sobre tu pedido #' . SITE_PREFIX . '-' . str_pad($pedido['id'], 5, '0', STR_PAD_LEFT)) ?>" 
           class="btn-admin btn-admin-primary" target="_blank">
            <i class="fa-brands fa-whatsapp"></i> Contactar cliente
        </a>
    </div>
</div>

<div class="panel-grid">
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-receipt"></i> Pedido #<?= SITE_PREFIX ?>-<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></h3>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:16px;">
            <div style="flex:1; min-width:200px;">
                <h4 style="font-size:13px; color:var(--admin-text-2); margin-bottom:6px;">Cliente</h4>
                <p style="font-weight:600;"><?= htmlspecialchars($pedido['nombre_cliente']) ?></p>
                <p style="font-size:13px; color:var(--admin-text-2);"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($pedido['celular']) ?></p>
            </div>
            <div style="flex:1; min-width:200px;">
                <h4 style="font-size:13px; color:var(--admin-text-2); margin-bottom:6px;">Dirección</h4>
                <p style="font-size:13px;"><?= htmlspecialchars($pedido['direccion']) ?></p>
                <p style="font-size:13px; color:var(--admin-text-2);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($pedido['referencia']) ?></p>
            </div>
            <div style="flex:1; min-width:200px;">
                <h4 style="font-size:13px; color:var(--admin-text-2); margin-bottom:6px;">Método de pago</h4>
                <p style="font-weight:600;"><?= ucfirst($pedido['metodo_pago']) ?></p>
                <p style="font-size:13px; color:var(--admin-text-2);"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></p>
            </div>
        </div>

        <hr style="margin:20px 0; border:none; border-top:1px solid var(--admin-border);">

        <h4 style="font-size:14px; margin-bottom:14px;">Productos del pedido</h4>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($detalles as $d): ?>
                <div style="display:flex; align-items:center; gap:12px; padding:10px; background:var(--admin-bg); border-radius:10px;">
                    <img src="<?= normalizarImagen($d['imagen'] ?: 'https://via.placeholder.com/40/0E5A3C/FFFFFF?text=Z') ?>" alt="" style="width:40px; height:40px; border-radius:8px; object-fit:cover;">
                    <div style="flex:1;">
                        <strong style="font-size:13px; display:block;"><?= htmlspecialchars($d['nombre_producto']) ?></strong>
                        <span style="font-size:12px; color:var(--admin-text-2);"><?= $d['cantidad'] ?> × S/ <?= number_format($d['precio_unitario'], 2) ?></span>
                    </div>
                    <div style="font-weight:700; color:var(--admin-verde);">S/ <?= number_format($d['subtotal'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:right; margin-top:20px; padding-top:16px; border-top:2px solid var(--admin-verde);">
            <strong style="font-size:18px; color:var(--admin-verde);">Total: S/ <?= number_format($pedido['total'], 2) ?></strong>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-gear"></i> Estado del pedido</h3>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Estado actual</label>
                <select name="estado" class="form-control">
                    <?php foreach ($ESTADOS_PEDIDO as $key => $est): ?>
                        <option value="<?= $key ?>" <?= $pedido['estado'] === $key ? 'selected' : '' ?>><?= $est['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="cambiar_estado" class="btn-admin btn-admin-primary btn-block">
                <i class="fa-solid fa-save"></i> Actualizar estado
            </button>
        </form>

        <div style="margin-top:24px; padding:14px; background:var(--admin-bg); border-radius:10px;">
            <h4 style="font-size:13px; color:var(--admin-text-2); margin-bottom:8px;"><i class="fa-solid fa-info-circle"></i> Información</h4>
            <p style="font-size:12px; color:var(--admin-text-2); line-height:1.6;">
                N° pedido: <strong>#<?= SITE_PREFIX ?>-<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong><br>
                Fecha: <strong><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></strong><br>
                Total: <strong>S/ <?= number_format($pedido['total'], 2) ?></strong>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
