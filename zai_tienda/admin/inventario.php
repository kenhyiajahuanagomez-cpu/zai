<?php
/**
 * BodegaViva Admin - Gestión de Inventario
 */
// Buffer al inicio, antes que cualquier salida
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Actualizar stock ANTES de incluir el header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_stock'])) {
    $producto_id = (int)$_POST['producto_id'];
    $nuevo_stock = (int)$_POST['stock'];
    $db->execute("UPDATE productos SET stock = ? WHERE id = ?", ['ii', $nuevo_stock, $producto_id]);
    flash('success', 'Stock actualizado: ' . $nuevo_stock . ' unidades.');
    header('Location: ' . SITE_URL . '/admin/inventario.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/inventario.php";</script>';
    exit;
}

$pagina_activa = 'inventario';
$pagina_titulo = 'Gestión de Inventario';
require_once __DIR__ . '/../includes/header_admin.php';

$stock_bajo = $db->fetchAll("SELECT * FROM productos WHERE stock < 10 AND stock > 0 ORDER BY stock ASC");
$agotados = $db->fetchAll("SELECT * FROM productos WHERE stock = 0");
$todos = $db->fetchAll("SELECT p.*, c.nombre AS categoria_nombre FROM productos p INNER JOIN categorias c ON p.categoria_id = c.id ORDER BY p.stock ASC");

$total_valor = $db->fetchOne("SELECT COALESCE(SUM(precio * stock), 0) AS total FROM productos")['total'];
$unidades_totales = $db->fetchOne("SELECT COALESCE(SUM(stock), 0) AS total FROM productos")['total'];
?>

<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : 'info' ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<!-- STATS INVENTARIO -->
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fa-solid fa-warehouse"></i></div>
        <div class="stat-label">Unidades totales</div>
        <div class="stat-value" data-counter="<?= $unidades_totales ?>"><?= $unidades_totales ?></div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-label">Valor inventario</div>
        <div class="stat-value">S/ <span data-counter="<?= $total_valor ?>" data-decimals="2"><?= number_format($total_valor, 2) ?></span></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-label">Stock bajo</div>
        <div class="stat-value" data-counter="<?= count($stock_bajo) ?>"><?= count($stock_bajo) ?></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
        <div class="stat-label">Agotados</div>
        <div class="stat-value" data-counter="<?= count($agotados) ?>"><?= count($agotados) ?></div>
    </div>
</div>

<!-- STOCK BAJO -->
<?php if (!empty($stock_bajo)): ?>
<div class="panel-card" style="border-left:4px solid var(--admin-naranja);">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Productos con stock bajo (< 10 unidades)</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock actual</th>
                    <th>Acción rápida</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stock_bajo as $p): ?>
                    <tr>
                        <td data-label="Producto">
                            <div class="product-cell">
                                <div class="product-thumb"><img src="<?= normalizarImagen($p['imagen']) ?>" alt="" onerror="this.src='https://via.placeholder.com/40/0E5A3C/FFFFFF?text=Z'"></div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                    <span><?= htmlspecialchars($p['marca']) ?> · <?= htmlspecialchars($p['presentacion']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Categoría"><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                        <td data-label="Stock"><span class="badge-status warning"><?= $p['stock'] ?> u.</span></td>
                        <td data-label="Acción">
                            <form method="POST" style="display:flex; gap:6px;">
                                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                                <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" style="width:70px; padding:6px; border:1px solid var(--admin-border); border-radius:6px;">
                                <button type="submit" name="actualizar_stock" class="btn-admin btn-admin-primary btn-admin-sm"><i class="fa-solid fa-check"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- AGOTADOS -->
<?php if (!empty($agotados)): ?>
<div class="panel-card" style="border-left:4px solid var(--admin-rojo); margin-top:20px;">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-ban"></i> Productos agotados</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Reabastecer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agotados as $p): ?>
                    <tr>
                        <td data-label="Producto">
                            <div class="product-cell">
                                <div class="product-thumb"><img src="<?= normalizarImagen($p['imagen']) ?>" alt=""></div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                    <span><?= htmlspecialchars($p['marca']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Precio">S/ <?= number_format($p['precio'], 2) ?></td>
                        <td data-label="Reabastecer">
                            <form method="POST" style="display:flex; gap:6px;">
                                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                                <input type="number" name="stock" value="50" min="0" style="width:70px; padding:6px; border:1px solid var(--admin-border); border-radius:6px;">
                                <button type="submit" name="actualizar_stock" class="btn-admin btn-admin-yellow btn-admin-sm"><i class="fa-solid fa-plus"></i> Reabastecer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- INVENTARIO COMPLETO -->
<div class="panel-card" style="margin-top:20px;">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-list"></i> Inventario completo</h3>
        <input type="text" id="tableSearch" placeholder="Buscar producto..." style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;">
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Actualizar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos as $p): ?>
                    <tr>
                        <td data-label="Producto">
                            <div class="product-cell">
                                <div class="product-thumb"><img src="<?= normalizarImagen($p['imagen']) ?>" alt="" onerror="this.src='https://via.placeholder.com/40/0E5A3C/FFFFFF?text=Z'"></div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                    <span><?= htmlspecialchars($p['marca']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Categoría"><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                        <td data-label="Precio">S/ <?= number_format($p['precio'], 2) ?></td>
                        <td data-label="Stock">
                            <?php if ($p['stock'] == 0): ?>
                                <span class="badge-status danger"><?= $p['stock'] ?></span>
                            <?php elseif ($p['stock'] < 10): ?>
                                <span class="badge-status warning"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge-status success"><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Valor">S/ <?= number_format($p['precio'] * $p['stock'], 2) ?></td>
                        <td data-label="Estado">
                            <?php if ($p['estado'] == 1): ?>
                                <span class="badge-status success">Activo</span>
                            <?php else: ?>
                                <span class="badge-status danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actualizar">
                            <form method="POST" style="display:flex; gap:6px;">
                                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                                <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" style="width:60px; padding:6px; border:1px solid var(--admin-border); border-radius:6px;">
                                <button type="submit" name="actualizar_stock" class="btn-admin btn-admin-primary btn-admin-sm"><i class="fa-solid fa-check"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
