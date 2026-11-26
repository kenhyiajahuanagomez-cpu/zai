<?php
/**
 * ZAI Admin - Gestión de Clientes
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header_admin.php';

$pagina_activa = 'clientes';
$pagina_titulo = 'Gestión de Clientes';

$busqueda = $_GET['q'] ?? '';
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM pedidos p WHERE p.cliente_id = c.id) AS total_pedidos,
        (SELECT COALESCE(SUM(total), 0) FROM pedidos p WHERE p.cliente_id = c.id AND p.estado = 'entregado') AS total_gastado
        FROM clientes c";
$params = [];
if ($busqueda) {
    $sql .= " WHERE c.nombre LIKE ? OR c.celular LIKE ?";
    $like = "%$busqueda%";
    $params = ['ss', $like, $like];
}
$sql .= " ORDER BY c.fecha_registro DESC";
$clientes = $db->fetchAll($sql, $params);

$total_clientes = count($clientes);
$gastado_total = 0;
foreach ($clientes as $c) $gastado_total += $c['total_gastado'];
?>

<div class="stats-grid">
    <div class="stat-card green"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-label">Clientes totales</div><div class="stat-value" data-counter="<?= $total_clientes ?>"><?= $total_clientes ?></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fa-solid fa-coins"></i></div><div class="stat-label">Gastado total</div><div class="stat-value">S/ <span data-counter="<?= $gastado_total ?>" data-decimals="2"><?= number_format($gastado_total, 2) ?></span></div></div>
    <div class="stat-card blue"><div class="stat-icon"><i class="fa-solid fa-shopping-bag"></i></div><div class="stat-label">Pedidos</div><div class="stat-value" data-counter="<?= array_sum(array_column($clientes, 'total_pedidos')) ?>"><?= array_sum(array_column($clientes, 'total_pedidos')) ?></div></div>
    <div class="stat-card orange"><div class="stat-icon"><i class="fa-solid fa-user-plus"></i></div><div class="stat-label">Nuevos (mes)</div><div class="stat-value" data-counter="<?= count($clientes) ?>"><?= count($clientes) ?></div></div>
</div>

<div class="filter-bar">
    <div class="left">
        <form method="GET" style="display:flex; gap:8px;">
            <input type="text" name="q" placeholder="Buscar cliente..." value="<?= htmlspecialchars($busqueda) ?>" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fa-solid fa-search"></i></button>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-users"></i> Clientes (<?= count($clientes) ?>)</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Pedidos</th>
                    <th>Gastado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td data-label="ID"><?= $c['id'] ?></td>
                        <td data-label="Cliente">
                            <div class="product-cell">
                                <div class="product-thumb" style="background:var(--admin-verde); color:white; display:flex; align-items:center; justify-content:center; font-weight:700;"><?= strtoupper(substr($c['nombre'], 0, 1)) ?></div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td data-label="Celular"><?= htmlspecialchars($c['celular']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($c['email'] ?: '—') ?></td>
                        <td data-label="Dirección" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($c['direccion'] ?: '—') ?></td>
                        <td data-label="Pedidos"><strong><?= $c['total_pedidos'] ?></strong></td>
                        <td data-label="Gastado"><strong style="color:var(--admin-verde);">S/ <?= number_format($c['total_gastado'], 2) ?></strong></td>
                        <td data-label="Registro"><?= date('d/m/Y', strtotime($c['fecha_registro'])) ?></td>
                        <td data-label="Acciones">
                            <div class="row-actions">
                                <a href="https://wa.me/51<?= preg_replace('/\D/', '', $c['celular']) ?>" class="icon-action" target="_blank" title="WhatsApp">
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
