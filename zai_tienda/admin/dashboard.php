<?php
/**
 * BodegaViva - Dashboard administrativo con filtros por fecha
 */
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Procesar petición AJAX para actualizar datos por fecha
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    header('Content-Type: application/json');
    $periodo = $_GET['periodo'] ?? '7';
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';
    
    $where = "1=1";
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $where = "fecha_pedido BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
    } else {
        $intervalos = ['hoy' => 'CURDATE()', '7' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 
                        '30' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)', 
                        '365' => 'DATE_SUB(CURDATE(), INTERVAL 365 DAY)'];
        $intervalo = $intervalos[$periodo] ?? $intervalos['7'];
        $where = "fecha_pedido >= $intervalo";
    }
    
    $stats = $db->fetchOne("SELECT COUNT(*) AS pedidos, COALESCE(SUM(total),0) AS ventas FROM pedidos WHERE $where");
    $ventas_dias = $db->fetchAll("SELECT DATE(fecha_pedido) as fecha, COUNT(*) as pedidos, COALESCE(SUM(total),0) as monto FROM pedidos WHERE $where GROUP BY DATE(fecha_pedido) ORDER BY fecha ASC");
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'ventas' => $ventas_dias
    ]);
    exit;
}

require_once __DIR__ . '/../includes/header_admin.php';

$pagina_activa = 'dashboard';
$pagina_titulo = 'Dashboard';

// Estadísticas principales (total acumulado)
$total_ventas = $db->fetchOne("SELECT COALESCE(SUM(monto), 0) AS total FROM ventas")['total'];
$total_pedidos = $db->fetchOne("SELECT COUNT(*) AS total FROM pedidos")['total'];
$total_productos = $db->fetchOne("SELECT COUNT(*) AS total FROM productos")['total'];
$total_clientes = $db->fetchOne("SELECT COUNT(*) AS total FROM clientes")['total'];
$pedidos_pendientes = $db->fetchOne("SELECT COUNT(*) AS total FROM pedidos WHERE estado = 'pendiente'")['total'];
$productos_stock_bajo = $db->fetchAll("SELECT * FROM productos WHERE stock < 10 ORDER BY stock ASC LIMIT 5");

// Ventas últimos 7 días (default)
$ventas_7dias = $db->fetchAll("
    SELECT DATE(fecha_pedido) as fecha, COUNT(*) as pedidos, COALESCE(SUM(total), 0) as monto 
    FROM pedidos 
    WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY DATE(fecha_pedido) 
    ORDER BY fecha ASC
");


// Productos más vendidos
$top_productos = $db->fetchAll("
    SELECT p.nombre, p.marca, p.imagen, p.vendidos, p.precio 
    FROM productos p 
    ORDER BY p.vendidos DESC 
    LIMIT 5
");

// Últimos pedidos
$ultimos_pedidos = $db->fetchAll("
    SELECT * FROM pedidos 
    ORDER BY fecha_pedido DESC 
    LIMIT 5
");

// Productos por categoría (para gráfico)
$prod_por_categoria = $db->fetchAll("
    SELECT c.nombre, COUNT(p.id) as cantidad 
    FROM categorias c 
    LEFT JOIN productos p ON c.id = p.categoria_id 
    GROUP BY c.id, c.nombre 
    ORDER BY cantidad DESC
");
?>

<!-- TARJETAS ESTADÍSTICAS -->
<div class="stats-grid">
    <div class="stat-card green reveal">
        <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-label">Ventas totales</div>
        <div class="stat-value">S/ <span data-counter="<?= $total_ventas ?>" data-decimals="2"><?= number_format($total_ventas, 2) ?></span></div>
        <div class="stat-trend up"><i class="fa-solid fa-arrow-up"></i> +18.2% vs mes anterior</div>
    </div>
    <div class="stat-card yellow reveal reveal-delay-1">
        <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
        <div class="stat-label">Pedidos totales</div>
        <div class="stat-value" data-counter="<?= $total_pedidos ?>"><?= $total_pedidos ?></div>
        <div class="stat-trend up"><i class="fa-solid fa-arrow-up"></i> <?= $pedidos_pendientes ?> pendientes</div>
    </div>
    <div class="stat-card blue reveal reveal-delay-2">
        <div class="stat-icon"><i class="fa-solid fa-box"></i></div>
        <div class="stat-label">Productos</div>
        <div class="stat-value" data-counter="<?= $total_productos ?>"><?= $total_productos ?></div>
        <div class="stat-trend up"><i class="fa-solid fa-arrow-up"></i> Catálogo activo</div>
    </div>
    <div class="stat-card orange reveal reveal-delay-3">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-label">Clientes</div>
        <div class="stat-value" data-counter="<?= $total_clientes ?>"><?= $total_clientes ?></div>
        <div class="stat-trend up"><i class="fa-solid fa-arrow-up"></i> +12 nuevos</div>
    </div>
</div>

<!-- ACCESOS RÁPIDOS -->
<div class="quick-access-grid">
    <a href="<?= SITE_URL ?>/admin/productos.php" class="quick-access">
        <div class="qa-icon bg-1"><i class="fa-solid fa-plus"></i></div>
        <span>Nuevo producto</span>
    </a>
    <a href="<?= SITE_URL ?>/admin/pedidos.php" class="quick-access">
        <div class="qa-icon bg-2"><i class="fa-solid fa-receipt"></i></div>
        <span>Ver pedidos</span>
    </a>
    <a href="<?= SITE_URL ?>/admin/inventario.php" class="quick-access">
        <div class="qa-icon bg-3"><i class="fa-solid fa-warehouse"></i></div>
        <span>Inventario</span>
    </a>
    <a href="<?= SITE_URL ?>/admin/reportes.php" class="quick-access">
        <div class="qa-icon bg-4"><i class="fa-solid fa-chart-pie"></i></div>
        <span>Reportes</span>
    </a>
</div>

<!-- GRÁFICOS Y TABLAS -->
<div class="panel-grid">
    <!-- Gráfico de ventas -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-chart-line"></i> <span id="tituloGrafico">Ventas de los últimos 7 días</span></h3>
            <div class="actions" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                <button onclick="cambiarPeriodo('hoy')" class="periodo-btn" data-p="hoy" style="padding:6px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:12px; background:var(--admin-card); color:var(--admin-text); cursor:pointer; transition:all 0.2s;">Hoy</button>
                <button onclick="cambiarPeriodo('7')" class="periodo-btn active" data-p="7" style="padding:6px 12px; border:1px solid var(--admin-verde); border-radius:6px; font-size:12px; background:var(--admin-verde); color:white; cursor:pointer; transition:all 0.2s;">7 días</button>
                <button onclick="cambiarPeriodo('30')" class="periodo-btn" data-p="30" style="padding:6px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:12px; background:var(--admin-card); color:var(--admin-text); cursor:pointer; transition:all 0.2s;">30 días</button>
                <button onclick="cambiarPeriodo('365')" class="periodo-btn" data-p="365" style="padding:6px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:12px; background:var(--admin-card); color:var(--admin-text); cursor:pointer; transition:all 0.2s;">1 año</button>
                <span style="color:var(--admin-text-2); font-size:11px;">|</span>
                <input type="date" id="fechaInicio" style="padding:4px 8px; border:1px solid var(--admin-border); border-radius:6px; font-size:11px;">
                <input type="date" id="fechaFin" style="padding:4px 8px; border:1px solid var(--admin-border); border-radius:6px; font-size:11px;">
                <button onclick="aplicarFechas()" style="padding:6px 12px; background:var(--admin-amarillo); color:var(--admin-verde); border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="ventasChart"></canvas>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-trophy"></i> Top productos</h3>
            <a href="<?= SITE_URL ?>/admin/reportes.php" style="font-size:13px; color:var(--admin-verde);">Ver todos</a>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($top_productos as $i => $p): ?>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:24px; height:24px; background:var(--admin-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:var(--admin-verde);"><?= $i + 1 ?></div>
                    <img src="<?= normalizarImagen($p['imagen']) ?>" alt="" style="width:36px; height:36px; border-radius:8px; object-fit:cover;" onerror="this.src='https://via.placeholder.com/36/0E5A3C/FFFFFF?text=Z'">
                    <div style="flex:1; min-width:0;">
                        <strong style="font-size:13px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($p['nombre']) ?></strong>
                        <span style="font-size:11px; color:var(--admin-text-2);"><?= htmlspecialchars($p['marca']) ?></span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px; font-weight:700; color:var(--admin-verde);"><?= $p['vendidos'] ?></div>
                        <div style="font-size:11px; color:var(--admin-text-2);">vendidos</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="panel-grid">
    <!-- Últimos pedidos -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Últimos pedidos</h3>
            <a href="<?= SITE_URL ?>/admin/pedidos.php" style="font-size:13px; color:var(--admin-verde);">Ver todos</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_pedidos as $ped): ?>
                        <tr>
                            <td data-label="Pedido"><strong>#<?= SITE_PREFIX ?>-<?= str_pad($ped['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td data-label="Cliente"><?= htmlspecialchars($ped['nombre_cliente']) ?></td>
                            <td data-label="Total"><strong>S/ <?= number_format($ped['total'], 2) ?></strong></td>
                            <td data-label="Estado">
                                <span class="badge-status <?= $ESTADOS_PEDIDO[$ped['estado']]['color'] ?>">
                                    <?= $ESTADOS_PEDIDO[$ped['estado']]['label'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Productos con stock bajo -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Stock bajo</h3>
            <a href="<?= SITE_URL ?>/admin/inventario.php" style="font-size:13px; color:var(--admin-verde);">Ver inventario</a>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($productos_stock_bajo as $p): ?>
                <div style="display:flex; align-items:center; gap:12px; padding:10px; background:rgba(229, 57, 53, 0.05); border-radius:10px; border-left:3px solid var(--admin-rojo);">
                    <img src="<?= normalizarImagen($p['imagen']) ?>" alt="" style="width:36px; height:36px; border-radius:8px; object-fit:cover;" onerror="this.src='https://via.placeholder.com/36/0E5A3C/FFFFFF?text=Z'">
                    <div style="flex:1; min-width:0;">
                        <strong style="font-size:13px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($p['nombre']) ?></strong>
                        <span style="font-size:11px; color:var(--admin-text-2);"><?= htmlspecialchars($p['marca']) ?></span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:14px; font-weight:700; color:var(--admin-rojo);"><?= $p['stock'] ?></div>
                        <div style="font-size:10px; color:var(--admin-text-2);">unidades</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Distribución por categoría -->
<div class="panel-card" style="margin-bottom:24px;">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-chart-pie"></i> Distribución de productos por categoría</h3>
    </div>
    <div class="chart-container" style="height:320px;">
        <canvas id="categoriasChart"></canvas>
    </div>
</div>

<script>
// ============ GRÁFICO DE VENTAS ============
const ventasData = <?= json_encode($ventas_7dias) ?>;
const ctxVentas = document.getElementById('ventasChart').getContext('2d');
const gradient = ctxVentas.createLinearGradient(0, 0, 0, 280);
gradient.addColorStop(0, 'rgba(123, 201, 80, 0.4)');
gradient.addColorStop(1, 'rgba(123, 201, 80, 0.05)');

new Chart(ctxVentas, {
    type: 'line',
    data: {
        labels: ventasData.map(v => {
            const d = new Date(v.fecha);
            return d.toLocaleDateString('es-PE', { weekday: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Ventas (S/)',
            data: ventasData.map(v => parseFloat(v.monto)),
            borderColor: '#0E5A3C',
            backgroundColor: gradient,
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#FFD23F',
            pointBorderColor: '#0E5A3C',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0E5A3C',
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 13 },
                bodyFont: { size: 13 },
                callbacks: {
                    label: function(ctx) { return 'S/ ' + ctx.parsed.y.toFixed(2); }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => 'S/ ' + v }
            }
        }
    }
});

// ============ GRÁFICO DE CATEGORÍAS ============
const catData = <?= json_encode($prod_por_categoria) ?>;
new Chart(document.getElementById('categoriasChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: catData.map(c => c.nombre),
        datasets: [{
            data: catData.map(c => c.cantidad),
            backgroundColor: ['#0E5A3C', '#7BC950', '#FFD23F', '#FB8C00', '#1976D2'],
            borderWidth: 4,
            borderColor: '#FFFFFF'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: { padding: 16, font: { size: 13 }, usePointStyle: true, pointStyle: 'circle' }
            },
            tooltip: {
                backgroundColor: '#0E5A3C',
                padding: 12,
                cornerRadius: 8
            }
        }
    }
});

function cambiarPeriodo(periodo) {
    // Actualizar botones
    document.querySelectorAll('.periodo-btn').forEach(function(btn) {
        if (btn.dataset.p === periodo) {
            btn.style.background = 'var(--admin-verde)';
            btn.style.color = 'white';
            btn.style.borderColor = 'var(--admin-verde)';
            btn.classList.add('active');
        } else {
            btn.style.background = 'var(--admin-card)';
            btn.style.color = 'var(--admin-text)';
            btn.style.borderColor = 'var(--admin-border)';
            btn.classList.remove('active');
        }
    });
    
    const titulos = {
        'hoy': 'Ventas de hoy',
        '7': 'Ventas de los últimos 7 días',
        '30': 'Ventas de los últimos 30 días',
        '365': 'Ventas del último año'
    };
    document.getElementById('tituloGrafico').textContent = titulos[periodo] || 'Ventas';
    
    // Cargar datos vía AJAX
    fetch('<?= SITE_URL ?>/admin/dashboard.php?ajax=stats&periodo=' + periodo)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                actualizarGrafico(data.ventas);
            }
        })
        .catch(function(e) {
            if (window.adminToast) adminToast('error', 'Error', 'No se pudieron cargar los datos.');
        });
}

function aplicarFechas() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;
    if (!inicio || !fin) {
        if (window.adminToast) adminToast('info', 'Selecciona fechas', 'Elige fecha inicio y fecha fin.');
        return;
    }
    document.getElementById('tituloGrafico').textContent = 'Ventas del ' + inicio + ' al ' + fin;
    // Desactivar botones de periodo
    document.querySelectorAll('.periodo-btn').forEach(function(btn) {
        btn.style.background = 'var(--admin-card)';
        btn.style.color = 'var(--admin-text)';
        btn.style.borderColor = 'var(--admin-border)';
        btn.classList.remove('active');
    });
    
    fetch('<?= SITE_URL ?>/admin/dashboard.php?ajax=stats&fecha_inicio=' + inicio + '&fecha_fin=' + fin)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                actualizarGrafico(data.ventas);
                if (window.adminToast) adminToast('success', 'Filtro aplicado', 'Periodo: ' + inicio + ' a ' + fin);
            }
        });
}

let ventasChartInstance = null;
function actualizarGrafico(ventasData) {
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(123, 201, 80, 0.4)');
    gradient.addColorStop(1, 'rgba(123, 201, 80, 0.05)');
    
    if (ventasChartInstance) ventasChartInstance.destroy();
    
    ventasChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ventasData.map(function(v) {
                const d = new Date(v.fecha);
                return d.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
            }),
            datasets: [{
                label: 'Ventas (S/)',
                data: ventasData.map(function(v) { return parseFloat(v.monto); }),
                borderColor: '#0E5A3C',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#FFD23F',
                pointBorderColor: '#0E5A3C',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0E5A3C',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) { return 'S/ ' + ctx.parsed.y.toFixed(2); }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return 'S/ ' + v; } } }
            },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
