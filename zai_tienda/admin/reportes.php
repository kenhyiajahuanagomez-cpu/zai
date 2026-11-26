<?php
/**
 * ZAI Admin - Reportes
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header_admin.php';

$pagina_activa = 'reportes';
$pagina_titulo = 'Reportes y Estadísticas';

$total_ventas = $db->fetchOne("SELECT COALESCE(SUM(monto), 0) AS t FROM ventas")['t'];
$total_pedidos = $db->fetchOne("SELECT COUNT(*) AS t FROM pedidos")['t'];
$ticket_promedio = $total_pedidos > 0 ? $total_ventas / $total_pedidos : 0;
$productos_vendidos = $db->fetchOne("SELECT COALESCE(SUM(cantidad), 0) AS t FROM detalle_pedidos")['t'];

// Top productos más vendidos
$top10 = $db->fetchAll("
    SELECT p.nombre, p.marca, p.imagen, p.vendidos, p.precio, 
           (p.vendidos * p.precio) AS ingresos
    FROM productos p 
    ORDER BY p.vendidos DESC 
    LIMIT 10
");

// Ventas por mes (últimos 6)
$ventas_mes = $db->fetchAll("
    SELECT DATE_FORMAT(fecha_venta, '%Y-%m') as mes, 
           DATE_FORMAT(fecha_venta, '%b %Y') as etiqueta,
           SUM(monto) as total, 
           COUNT(*) as cantidad 
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m')
    ORDER BY mes ASC
");

// Productos por categoría
$cat_distribucion = $db->fetchAll("
    SELECT c.nombre, COUNT(p.id) as productos, 
           COALESCE(SUM(p.vendidos), 0) as vendidos
    FROM categorias c 
    LEFT JOIN productos p ON c.id = p.categoria_id 
    GROUP BY c.id 
    ORDER BY vendidos DESC
");
?>

<div class="stats-grid">
    <div class="stat-card green"><div class="stat-icon"><i class="fa-solid fa-coins"></i></div><div class="stat-label">Ingresos totales</div><div class="stat-value">S/ <span data-counter="<?= $total_ventas ?>" data-decimals="2"><?= number_format($total_ventas, 2) ?></span></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fa-solid fa-receipt"></i></div><div class="stat-label">Pedidos totales</div><div class="stat-value" data-counter="<?= $total_pedidos ?>"><?= $total_pedidos ?></div></div>
    <div class="stat-card blue"><div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div><div class="stat-label">Ticket promedio</div><div class="stat-value">S/ <span data-counter="<?= $ticket_promedio ?>" data-decimals="2"><?= number_format($ticket_promedio, 2) ?></span></div></div>
    <div class="stat-card orange"><div class="stat-icon"><i class="fa-solid fa-box"></i></div><div class="stat-label">Productos vendidos</div><div class="stat-value" data-counter="<?= $productos_vendidos ?>"><?= $productos_vendidos ?></div></div>
</div>

<div class="panel-card" style="margin-bottom:24px;">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-chart-line"></i> Evolución de ventas (6 meses)</h3>
        <button class="btn-admin btn-admin-outline btn-admin-sm" onclick="window.print()"><i class="fa-solid fa-download"></i> Exportar</button>
    </div>
    <div class="chart-container" style="height:300px;">
        <canvas id="evolucionChart"></canvas>
    </div>
</div>

<div class="panel-grid">
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-trophy"></i> Top 10 productos más vendidos</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Vendidos</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top10 as $i => $p): ?>
                        <tr>
                            <td data-label="#"><strong><?= $i + 1 ?></strong></td>
                            <td data-label="Producto">
                                <div class="product-cell">
                                    <img src="<?= normalizarImagen($p['imagen']) ?>" alt="" style="width:32px; height:32px; border-radius:6px;" onerror="this.src='https://via.placeholder.com/32/0E5A3C/FFFFFF?text=Z'">
                                    <div class="product-info"><strong><?= htmlspecialchars($p['nombre']) ?></strong><span><?= htmlspecialchars($p['marca']) ?></span></div>
                                </div>
                            </td>
                            <td data-label="Vendidos"><strong><?= $p['vendidos'] ?></strong></td>
                            <td data-label="Ingresos"><strong style="color:var(--admin-verde);">S/ <?= number_format($p['ingresos'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-chart-pie"></i> Ventas por categoría</h3>
        </div>
        <div class="chart-container">
            <canvas id="catDistChart"></canvas>
        </div>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-list"></i> Detalle por categoría</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Productos</th>
                    <th>Unidades vendidas</th>
                    <th>Participación</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_vendidos_cat = array_sum(array_column($cat_distribucion, 'vendidos'));
                foreach ($cat_distribucion as $c): 
                    $participacion = $total_vendidos_cat > 0 ? ($c['vendidos'] / $total_vendidos_cat) * 100 : 0;
                ?>
                    <tr>
                        <td data-label="Categoría"><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td data-label="Productos"><?= $c['productos'] ?></td>
                        <td data-label="Vendidos"><?= $c['vendidos'] ?></td>
                        <td data-label="Participación">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="flex:1; height:8px; background:var(--admin-bg); border-radius:4px; overflow:hidden;">
                                    <div style="width:<?= $participacion ?>%; height:100%; background:var(--admin-verde);"></div>
                                </div>
                                <strong><?= number_format($participacion, 1) ?>%</strong>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const ventasMes = <?= json_encode($ventas_mes) ?>;
const catDist = <?= json_encode($cat_distribucion) ?>;

new Chart(document.getElementById('evolucionChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: ventasMes.map(v => v.etiqueta),
        datasets: [{
            label: 'Ventas (S/)',
            data: ventasMes.map(v => parseFloat(v.total)),
            borderColor: '#0E5A3C',
            backgroundColor: 'rgba(123, 201, 80, 0.2)',
            borderWidth: 3, tension: 0.4, fill: true,
            pointBackgroundColor: '#FFD23F',
            pointBorderColor: '#0E5A3C',
            pointBorderWidth: 2, pointRadius: 5
        }, {
            label: 'Cantidad de pedidos',
            data: ventasMes.map(v => v.cantidad),
            borderColor: '#FB8C00',
            backgroundColor: 'transparent',
            borderWidth: 2, tension: 0.4,
            borderDash: [5, 5],
            pointBackgroundColor: '#FB8C00',
            pointRadius: 4, yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, position: 'left', ticks: { callback: v => 'S/ ' + v } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});

new Chart(document.getElementById('catDistChart').getContext('2d'), {
    type: 'polarArea',
    data: {
        labels: catDist.map(c => c.nombre),
        datasets: [{
            data: catDist.map(c => c.vendidos),
            backgroundColor: ['#0E5A3C', '#7BC950', '#FFD23F', '#FB8C00', '#1976D2']
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { padding: 10, font: { size: 11 }, usePointStyle: true } } }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
