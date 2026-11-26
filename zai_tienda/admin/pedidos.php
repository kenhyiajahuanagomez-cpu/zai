<?php
/**
 * ZAI Admin - Gestión de Pedidos
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header_admin.php';

$pagina_activa = 'pedidos';
$pagina_titulo = 'Gestión de Pedidos';

// Cambiar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];
    $db->execute("UPDATE pedidos SET estado = ? WHERE id = ?", ['si', $nuevo_estado, $pedido_id]);
    flash('success', 'Estado del pedido actualizado.');
    redirigir(SITE_URL . '/admin/pedidos.php');
}

$filtro = $_GET['estado'] ?? '';
$sql = "SELECT * FROM pedidos";
$params = [];
if ($filtro && in_array($filtro, ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'])) {
    $sql .= " WHERE estado = ?";
    $params = ['s', $filtro];
}
$sql .= " ORDER BY fecha_pedido DESC";
$pedidos = $db->fetchAll($sql, $params);

$pedidos_pendientes = $db->fetchOne("SELECT COUNT(*) AS t FROM pedidos WHERE estado='pendiente'")['t'];
$pedidos_procesando = $db->fetchOne("SELECT COUNT(*) AS t FROM pedidos WHERE estado='procesando'")['t'];
$pedidos_enviados = $db->fetchOne("SELECT COUNT(*) AS t FROM pedidos WHERE estado='enviado'")['t'];
$pedidos_entregados = $db->fetchOne("SELECT COUNT(*) AS t FROM pedidos WHERE estado='entregado'")['t'];
?>

<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : 'info' ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card yellow"><div class="stat-icon"><i class="fa-solid fa-clock"></i></div><div class="stat-label">Pendientes</div><div class="stat-value"><?= $pedidos_pendientes ?></div></div>
    <div class="stat-card blue"><div class="stat-icon"><i class="fa-solid fa-gear"></i></div><div class="stat-label">Procesando</div><div class="stat-value"><?= $pedidos_procesando ?></div></div>
    <div class="stat-card orange"><div class="stat-icon"><i class="fa-solid fa-truck"></i></div><div class="stat-label">Enviados</div><div class="stat-value"><?= $pedidos_enviados ?></div></div>
    <div class="stat-card green"><div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div><div class="stat-label">Entregados</div><div class="stat-value"><?= $pedidos_entregados ?></div></div>
</div>

<!-- FILTROS -->
<div class="filter-bar">
    <div class="left">
        <a href="<?= SITE_URL ?>/admin/pedidos.php" class="btn-admin btn-admin-outline <?= !$filtro ? 'btn-admin-primary' : '' ?>">Todos</a>
        <?php foreach (['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'] as $est): ?>
            <a href="<?= SITE_URL ?>/admin/pedidos.php?estado=<?= $est ?>" class="btn-admin btn-admin-outline <?= $filtro === $est ? 'btn-admin-primary' : '' ?>">
                <?= $ESTADOS_PEDIDO[$est]['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="right">
        <input type="text" id="tableSearch" placeholder="Buscar pedido..." style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;">
    </div>
</div>

<!-- TABLA PEDIDOS -->
<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-receipt"></i> Pedidos (<?= count($pedidos) ?>)</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Dirección</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $ped): ?>
                    <tr>
                        <td data-label="Pedido"><strong>#<?= SITE_PREFIX ?>-<?= str_pad($ped['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td data-label="Cliente"><?= htmlspecialchars($ped['nombre_cliente']) ?></td>
                        <td data-label="Celular"><?= htmlspecialchars($ped['celular']) ?></td>
                        <td data-label="Dirección" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($ped['direccion']) ?></td>
                        <td data-label="Total"><strong>S/ <?= number_format($ped['total'], 2) ?></strong></td>
                        <td data-label="Pago"><span class="badge-status primary"><?= ucfirst($ped['metodo_pago']) ?></span></td>
                        <td data-label="Fecha"><?= date('d/m/Y H:i', strtotime($ped['fecha_pedido'])) ?></td>
                        <td data-label="Estado">
                            <span class="badge-status <?= $ESTADOS_PEDIDO[$ped['estado']]['color'] ?>">
                                <?= $ESTADOS_PEDIDO[$ped['estado']]['label'] ?>
                            </span>
                        </td>
                        <td data-label="Acciones">
                            <div class="row-actions">
                                <a href="<?= SITE_URL ?>/admin/pedido_detalle.php?id=<?= $ped['id'] ?>" class="icon-action" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="https://wa.me/51<?= preg_replace('/\D/', '', $ped['celular']) ?>?text=<?= urlencode('Hola ' . $ped['nombre_cliente'] . ', tu pedido #' . SITE_PREFIX . '-' . str_pad($ped['id'], 5, '0', STR_PAD_LEFT) . ' está en proceso.') ?>" 
                                   class="icon-action" target="_blank" title="WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
