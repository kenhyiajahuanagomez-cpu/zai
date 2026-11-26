<?php
/**
 * BodegaViva Admin - Gestión de Productos
 */
// Buffer al inicio, antes que cualquier salida
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Procesar eliminación ANTES de incluir el header
$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($accion === 'eliminar' && $id > 0) {
    $db->execute("DELETE FROM productos WHERE id = ?", ['i', $id]);
    flash('success', 'Producto eliminado correctamente.');
    header('Location: ' . SITE_URL . '/admin/productos.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/productos.php";</script>';
    exit;
}

// ============ EDICIÓN MASIVA ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_masiva'])) {
    $ids = $_POST['seleccionados'] ?? [];
    $accion_masiva = $_POST['accion_masiva'];
    
    if (empty($ids)) {
        flash('error', 'Selecciona al menos un producto.');
    } else {
        $ids_str = implode(',', array_map('intval', $ids));
        
        if ($accion_masiva === 'eliminar') {
            $db->execute("DELETE FROM productos WHERE id IN ($ids_str)", []);
            flash('success', count($ids) . ' productos eliminados.');
        } elseif ($accion_masiva === 'activar') {
            $db->execute("UPDATE productos SET estado = 1 WHERE id IN ($ids_str)", []);
            flash('success', count($ids) . ' productos activados.');
        } elseif ($accion_masiva === 'desactivar') {
            $db->execute("UPDATE productos SET estado = 0 WHERE id IN ($ids_str)", []);
            flash('success', count($ids) . ' productos desactivados.');
        } elseif ($accion_masiva === 'destacar') {
            $db->execute("UPDATE productos SET destacado = 1 WHERE id IN ($ids_str)", []);
            flash('success', count($ids) . ' productos marcados como destacados.');
        } elseif ($accion_masiva === 'oferta') {
            $db->execute("UPDATE productos SET en_oferta = 1 WHERE id IN ($ids_str)", []);
            flash('success', count($ids) . ' productos marcados en oferta.');
        } elseif ($accion_masiva === 'cambiar_precio') {
            $nuevo_precio = (float)($_POST['nuevo_precio'] ?? 0);
            $tipo_cambio = $_POST['tipo_cambio'] ?? 'fijo';
            if ($nuevo_precio > 0) {
                if ($tipo_cambio === 'fijo') {
                    $db->execute("UPDATE productos SET precio = ? WHERE id IN ($ids_str)", ['d', $nuevo_precio]);
                } elseif ($tipo_cambio === 'porcentaje') {
                    // Aplicar porcentaje (ej. 10 = +10%, -10 = -10%)
                    $factor = 1 + ($nuevo_precio / 100);
                    $db->execute("UPDATE productos SET precio = ROUND(precio * ?, 2) WHERE id IN ($ids_str)", ['d', $factor]);
                }
                flash('success', 'Precios actualizados en ' . count($ids) . ' productos.');
            }
        } elseif ($accion_masiva === 'cambiar_categoria') {
            $nueva_cat = (int)($_POST['nueva_categoria'] ?? 0);
            if ($nueva_cat > 0) {
                $db->execute("UPDATE productos SET categoria_id = ? WHERE id IN ($ids_str)", ['i', $nueva_cat]);
                flash('success', 'Categoría actualizada en ' . count($ids) . ' productos.');
            }
        }
    }
    header('Location: ' . SITE_URL . '/admin/productos.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/productos.php";</script>';
    exit;
}

$pagina_activa = 'productos';
$pagina_titulo = 'Gestión de Productos';
require_once __DIR__ . '/../includes/header_admin.php';

// Filtros
$busqueda = $_GET['q'] ?? '';
$cat_filtro = (int)($_GET['cat'] ?? 0);

$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM productos p 
        INNER JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";
$params = [];
if ($busqueda) {
    $sql .= " AND (p.nombre LIKE ? OR p.marca LIKE ?)";
    $like = "%$busqueda%";
    $params = ['ss', $like, $like];
}
if ($cat_filtro) {
    $sql .= " AND p.categoria_id = ?";
    if (empty($params)) $params = ['i', $cat_filtro];
    else { $params[0] .= 'i'; $params[] = $cat_filtro; }
}
$sql .= " ORDER BY p.id DESC";

$productos = $db->fetchAll($sql, $params);
$categorias = obtenerCategorias();
?>

<!-- ALERTAS -->
<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : 'info' ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<!-- FILTROS Y ACCIONES -->
<div class="filter-bar">
    <div class="left">
        <form method="GET" style="display:flex; gap:8px;">
            <input type="text" name="q" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;">
            <select name="cat" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $cat_filtro == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fa-solid fa-search"></i></button>
        </form>
    </div>
    <div class="right">
        <a href="<?= SITE_URL ?>/admin/producto_form.php" class="btn-admin btn-admin-primary">
            <i class="fa-solid fa-plus"></i> Nuevo producto
        </a>
    </div>
</div>

<!-- BARRA DE ACCIONES MASIVAS -->
<div class="panel-card" style="margin-bottom:20px; background:linear-gradient(135deg, var(--admin-sidebar), var(--admin-verde-claro)); padding:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
    <div style="color:white; display:flex; align-items:center; gap:10px;">
        <i class="fa-solid fa-layer-group" style="font-size:20px; color:var(--admin-amarillo);"></i>
        <strong>Acciones masivas:</strong>
        <span id="contadorSeleccion" style="background:var(--admin-amarillo); color:var(--admin-verde); padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700;">0 seleccionados</span>
    </div>
    <div style="display:flex; gap:6px; flex:1; flex-wrap:wrap;">
        <form method="POST" id="formMasivo" style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="accion_masiva" id="accionInput">
            <div id="camposExtra"></div>
            <button type="button" onclick="ejecutarMasivo('activar')" class="btn-admin btn-admin-sm" style="background:var(--admin-verde-claro); color:white;">
                <i class="fa-solid fa-check"></i> Activar
            </button>
            <button type="button" onclick="ejecutarMasivo('desactivar')" class="btn-admin btn-admin-sm" style="background:var(--admin-text-2); color:white;">
                <i class="fa-solid fa-pause"></i> Desactivar
            </button>
            <button type="button" onclick="ejecutarMasivo('destacar')" class="btn-admin btn-admin-sm" style="background:var(--admin-amarillo); color:var(--admin-verde);">
                <i class="fa-solid fa-star"></i> Destacar
            </button>
            <button type="button" onclick="ejecutarMasivo('oferta')" class="btn-admin btn-admin-sm" style="background:var(--admin-rojo); color:white;">
                <i class="fa-solid fa-tag"></i> En oferta
            </button>
            <button type="button" onclick="mostrarCambioPrecio()" class="btn-admin btn-admin-sm" style="background:var(--admin-azul); color:white;">
                <i class="fa-solid fa-coins"></i> Cambiar precio
            </button>
            <button type="button" onclick="mostrarCambioCategoria()" class="btn-admin btn-admin-sm" style="background:var(--admin-naranja); color:white;">
                <i class="fa-solid fa-layer-group"></i> Cambiar categoría
            </button>
            <button type="button" onclick="confirmarEliminarMasivo()" class="btn-admin btn-admin-sm" style="background:var(--admin-rojo); color:white;">
                <i class="fa-solid fa-trash"></i> Eliminar
            </button>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-box"></i> Productos registrados (<?= count($productos) ?>)</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="selectTodos" onchange="seleccionarTodos(this)" style="width:18px; height:18px; cursor:pointer;">
                    </th>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Vendidos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--admin-text-2);">No se encontraron productos.</td></tr>
                <?php else: foreach ($productos as $p): ?>
                    <tr>
                        <td data-label="Seleccionar">
                            <input type="checkbox" name="seleccionados[]" value="<?= $p['id'] ?>" class="check-producto" onchange="actualizarContador()" style="width:18px; height:18px; cursor:pointer;">
                        </td>
                        <td data-label="ID"><?= $p['id'] ?></td>
                        <td data-label="Producto">
                            <div class="product-cell">
                                <div class="product-thumb">
                                    <img src="<?= normalizarImagen($p['imagen']) ?>" alt="" onerror="this.src='https://via.placeholder.com/40/0E5A3C/FFFFFF?text=Z'">
                                </div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                    <span><?= htmlspecialchars($p['marca']) ?> · <?= htmlspecialchars($p['presentacion']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Categoría"><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                        <td data-label="Precio"><strong>S/ <?= number_format($p['precio'], 2) ?></strong></td>
                        <td data-label="Stock">
                            <?php if ($p['stock'] < 10): ?>
                                <span class="badge-status danger"><?= $p['stock'] ?> u.</span>
                            <?php else: ?>
                                <span class="badge-status success"><?= $p['stock'] ?> u.</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Vendidos"><?= $p['vendidos'] ?></td>
                        <td data-label="Estado">
                            <?php if ($p['estado'] == 1): ?>
                                <span class="badge-status success">Activo</span>
                            <?php else: ?>
                                <span class="badge-status danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Acciones">
                            <div class="row-actions">
                                <a href="<?= SITE_URL ?>/admin/producto_form.php?id=<?= $p['id'] ?>" class="icon-action" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/productos.php?accion=eliminar&id=<?= $p['id'] ?>" 
                                   class="icon-action danger" title="Eliminar" data-confirm="¿Eliminar el producto <?= htmlspecialchars($p['nombre']) ?>?">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ============ SELECCIÓN MASIVA ============
function seleccionarTodos(check) {
    document.querySelectorAll('.check-producto').forEach(function(cb) {
        cb.checked = check.checked;
    });
    actualizarContador();
}

function actualizarContador() {
    const checks = document.querySelectorAll('.check-producto:checked');
    document.getElementById('contadorSeleccion').textContent = checks.length + ' seleccionados';
}

function obtenerSeleccionados() {
    return Array.from(document.querySelectorAll('.check-producto:checked')).map(function(cb) {
        return cb.value;
    });
}

function ejecutarMasivo(accion) {
    const ids = obtenerSeleccionados();
    if (ids.length === 0) {
        if (window.adminToast) adminToast('error', 'Sin selección', 'Marca al menos un producto.');
        return;
    }
    // Crear formulario dinámico
    const form = document.getElementById('formMasivo');
    // Limpiar campos extra previos
    document.getElementById('camposExtra').innerHTML = '';
    document.getElementById('accionInput').value = accion;
    
    // Agregar IDs seleccionados como campos ocultos
    ids.forEach(function(id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'seleccionados[]';
        input.value = id;
        document.getElementById('camposExtra').appendChild(input);
    });
    
    form.submit();
}

function mostrarCambioPrecio() {
    const ids = obtenerSeleccionados();
    if (ids.length === 0) {
        if (window.adminToast) adminToast('error', 'Sin selección', 'Marca al menos un producto.');
        return;
    }
    
    // Mostrar modal con opciones
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
        <div class="modal" style="max-width:420px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-coins"></i> Cambiar precio a ${ids.length} productos</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" onsubmit="return enviarCambioPrecio(this)">
                <input type="hidden" name="accion_masiva" value="cambiar_precio">
                ${ids.map(id => `<input type="hidden" name="seleccionados[]" value="${id}">`).join('')}
                <div class="form-group">
                    <label>Tipo de cambio</label>
                    <select name="tipo_cambio" class="form-control">
                        <option value="fijo">Precio fijo (todos al mismo precio)</option>
                        <option value="porcentaje">Porcentaje (+10 = +10%, -10 = -10%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor</label>
                    <input type="number" step="0.01" name="nuevo_precio" class="form-control" required placeholder="Ej: 5.50 o 10">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-outline" onclick="this.closest('.modal-overlay').remove()">Cancelar</button>
                    <button type="submit" class="btn-admin btn-admin-primary"><i class="fa-solid fa-check"></i> Aplicar</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

function enviarCambioPrecio(form) {
    return true; // El form se envía normalmente
}

function mostrarCambioCategoria() {
    const ids = obtenerSeleccionados();
    if (ids.length === 0) {
        if (window.adminToast) adminToast('error', 'Sin selección', 'Marca al menos un producto.');
        return;
    }
    
    const cats = <?= json_encode(obtenerCategorias()) ?>;
    const opciones = cats.map(function(c) { 
        return `<option value="${c.id}">${c.nombre}</option>`; 
    }).join('');
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
        <div class="modal" style="max-width:420px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-layer-group"></i> Cambiar categoría a ${ids.length} productos</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion_masiva" value="cambiar_categoria">
                ${ids.map(id => `<input type="hidden" name="seleccionados[]" value="${id}">`).join('')}
                <div class="form-group">
                    <label>Nueva categoría</label>
                    <select name="nueva_categoria" class="form-control" required>
                        <option value="">Selecciona...</option>
                        ${opciones}
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-outline" onclick="this.closest('.modal-overlay').remove()">Cancelar</button>
                    <button type="submit" class="btn-admin btn-admin-primary"><i class="fa-solid fa-check"></i> Aplicar</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

function confirmarEliminarMasivo() {
    const ids = obtenerSeleccionados();
    if (ids.length === 0) {
        if (window.adminToast) adminToast('error', 'Sin selección', 'Marca al menos un producto.');
        return;
    }
    if (!confirm('¿ELIMINAR ' + ids.length + ' productos? Esta acción NO se puede deshacer.')) return;
    ejecutarMasivo('eliminar');
}
</script>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
