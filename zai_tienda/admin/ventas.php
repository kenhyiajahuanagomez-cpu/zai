<?php
/**
 * ZAI Admin - Ventas
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header_admin.php';

$pagina_activa = 'ventas';
$pagina_titulo = 'Gestión de Ventas';

$ventas = $db->fetchAll("
    SELECT v.*, p.nombre_cliente, p.estado, p.id AS pedido_id 
    FROM ventas v 
    INNER JOIN pedidos p ON v.pedido_id = p.id 
    ORDER BY v.fecha_venta DESC
");

$total_mes = $db->fetchOne("SELECT COALESCE(SUM(monto), 0) AS t FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURDATE())")['t'];
$total_hoy = $db->fetchOne("SELECT COALESCE(SUM(monto), 0) AS t FROM ventas WHERE fecha_venta = CURDATE()")['t'];
$promedio = $db->fetchOne("SELECT COALESCE(AVG(monto), 0) AS t FROM ventas")['t'];
$total_ventas_count = count($ventas);

// Por método de pago
$por_metodo = $db->fetchAll("
    SELECT metodo_pago, COUNT(*) AS cantidad, SUM(monto) AS total 
    FROM ventas 
    GROUP BY metodo_pago
");
?>

<div class="stats-grid">
    <div class="stat-card green"><div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div><div class="stat-label">Ventas hoy</div><div class="stat-value">S/ <span data-counter="<?= $total_hoy ?>" data-decimals="2"><?= number_format($total_hoy, 2) ?></span></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fa-solid fa-calendar"></i></div><div class="stat-label">Ventas del mes</div><div class="stat-value">S/ <span data-counter="<?= $total_mes ?>" data-decimals="2"><?= number_format($total_mes, 2) ?></span></div></div>
    <div class="stat-card blue"><div class="stat-icon"><i class="fa-solid fa-receipt"></i></div><div class="stat-label">Total ventas</div><div class="stat-value" data-counter="<?= $total_ventas_count ?>"><?= $total_ventas_count ?></div></div>
    <div class="stat-card orange"><div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div><div class="stat-label">Promedio</div><div class="stat-value">S/ <span data-counter="<?= $promedio ?>" data-decimals="2"><?= number_format($promedio, 2) ?></span></div></div>
</div>

<div class="panel-grid">
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-chart-bar"></i> Ventas por método de pago</h3>
        </div>
        <div class="chart-container">
            <canvas id="metodoChart"></canvas>
        </div>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-pie-chart"></i> Distribución</h3>
        </div>
        <div class="chart-container">
            <canvas id="metodoPie"></canvas>
        </div>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-list"></i> Historial de ventas</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td data-label="ID"><?= $v['id'] ?></td>
                        <td data-label="Pedido"><a href="<?= SITE_URL ?>/admin/pedido_detalle.php?id=<?= $v['pedido_id'] ?>" style="color:var(--admin-verde); font-weight:600;">#<?= SITE_PREFIX ?>-<?= str_pad($v['pedido_id'], 5, '0', STR_PAD_LEFT) ?></a></td>
                        <td data-label="Cliente"><?= htmlspecialchars($v['nombre_cliente']) ?></td>
                        <td data-label="Fecha"><?= date('d/m/Y', strtotime($v['fecha_venta'])) ?></td>
                        <td data-label="Monto"><strong style="color:var(--admin-verde);">S/ <?= number_format($v['monto'], 2) ?></strong></td>
                        <td data-label="Método"><span class="badge-status primary"><?= ucfirst($v['metodo_pago']) ?></span></td>
                        <td data-label="Estado"><span class="badge-status <?= $ESTADOS_PEDIDO[$v['estado']]['color'] ?>"><?= $ESTADOS_PEDIDO[$v['estado']]['label'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const porMetodo = <?= json_encode($por_metodo) ?>;
const colores = {
    'efectivo': '#7BC950',
    'yape': '#7D3C9B',
    'plin': '#00A0DF',
    'transferencia': '#FB8C00'
};
new Chart(document.getElementById('metodoChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: porMetodo.map(m => m.metodo_pago.charAt(0).toUpperCase() + m.metodo_pago.slice(1)),
        datasets: [{
            label: 'Total (S/)',
            data: porMetodo.map(m => parseFloat(m.total)),
            backgroundColor: porMetodo.map(m => colores[m.metodo_pago] || '#0E5A3C'),
            borderRadius: 8
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } } }
    }
});
new Chart(document.getElementById('metodoPie').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: porMetodo.map(m => m.metodo_pago.charAt(0).toUpperCase() + m.metodo_pago.slice(1)),
        datasets: [{
            data: porMetodo.map(m => m.cantidad),
            backgroundColor: porMetodo.map(m => colores[m.metodo_pago] || '#0E5A3C'),
            borderWidth: 4, borderColor: '#FFF'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { padding: 12, usePointStyle: true, pointStyle: 'circle' } } }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
