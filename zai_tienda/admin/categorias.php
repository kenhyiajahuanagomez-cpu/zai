<?php
/**
 * BodegaViva Admin - Gestión de Categorías
 */
// Buffer al inicio, antes que cualquier salida
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$accion = $_GET['accion'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// ============ PROCESAR ACCIONES ANTES DEL HEADER ============
if ($accion === 'eliminar' && $id > 0) {
    try {
        $db->execute("DELETE FROM categorias WHERE id = ?", ['i', $id]);
        flash('success', 'Categoría eliminada.');
    } catch (Exception $e) {
        flash('error', 'No se puede eliminar: tiene productos asociados.');
    }
    header('Location: ' . SITE_URL . '/admin/categorias.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/categorias.php";</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $icono = trim($_POST['icono'] ?? 'fa-tag');
    $imagen = $_POST['imagen_url'] ?? '';
    $id_edit = (int)($_POST['id'] ?? 0);
    
    // Subir imagen si se cargó
    if (!empty($_FILES['imagen_file']['name']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagen_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $carpeta = __DIR__ . '/../img/categorias/';
            if (!is_dir($carpeta)) @mkdir($carpeta, 0755, true);
            $nombre_archivo = uniqid('cat_') . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen_file']['tmp_name'], $carpeta . $nombre_archivo)) {
                $imagen = SITE_URL . '/img/categorias/' . $nombre_archivo;
            }
        }
    }
    if (empty($imagen)) $imagen = 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80';

    $nombre_db = $conn->real_escape_string($nombre);
    $descripcion_db = $conn->real_escape_string($descripcion);
    $icono_db = $conn->real_escape_string($icono);
    $imagen_db = $conn->real_escape_string($imagen);

    if ($id_edit > 0) {
        $stmt = $conn->prepare("UPDATE categorias SET nombre=?, descripcion=?, icono=?, imagen=? WHERE id=?");
        $stmt->bind_param('ssssi', $nombre_db, $descripcion_db, $icono_db, $imagen_db, $id_edit);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Categoría actualizada.');
    } else {
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, icono, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nombre_db, $descripcion_db, $icono_db, $imagen_db);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Categoría creada.');
    }
    header('Location: ' . SITE_URL . '/admin/categorias.php');
    echo '<script>window.location.href="' . SITE_URL . '/admin/categorias.php";</script>';
    exit;
}

// ============ AHORA SÍ INCLUIR EL HEADER ============
$pagina_activa = 'categorias';
$pagina_titulo = 'Gestión de Categorías';
require_once __DIR__ . '/../includes/header_admin.php';

$categorias = $db->fetchAll("
    SELECT c.*, COUNT(p.id) AS total_productos 
    FROM categorias c 
    LEFT JOIN productos p ON c.id = p.categoria_id 
    GROUP BY c.id 
    ORDER BY c.nombre
");
?>

<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : ($f['tipo'] === 'error' ? 'xmark' : 'info') ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<div class="filter-bar">
    <div class="left"><h3 style="margin:0; font-size:18px; color:var(--admin-text);">Total: <?= count($categorias) ?> categorías</h3></div>
    <div class="right">
        <button onclick="openModal('modalCategoria')" class="btn-admin btn-admin-primary">
            <i class="fa-solid fa-plus"></i> Nueva categoría
        </button>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header">
        <h3><i class="fa-solid fa-layer-group"></i> Categorías</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Productos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td data-label="ID"><?= $c['id'] ?></td>
                        <td data-label="Categoría">
                            <div class="product-cell">
                                <div class="product-thumb">
                                    <img src="<?= normalizarImagen($c['imagen']) ?>" alt="" onerror="this.src='https://via.placeholder.com/40/0E5A3C/FFFFFF?text=C'">
                                </div>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                                    <span><i class="fa-solid <?= htmlspecialchars($c['icono']) ?>"></i> Icono</span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Descripción"><?= htmlspecialchars(acortar($c['descripcion'], 60)) ?></td>
                        <td data-label="Productos"><strong><?= $c['total_productos'] ?></strong></td>
                        <td data-label="Estado">
                            <?php if ($c['estado'] == 1): ?>
                                <span class="badge-status success">Activo</span>
                            <?php else: ?>
                                <span class="badge-status danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Acciones">
                            <div class="row-actions">
                                <button onclick='editarCategoria(<?= json_encode($c, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' class="icon-action" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="<?= SITE_URL ?>/admin/categorias.php?accion=eliminar&id=<?= $c['id'] ?>" 
                                   class="icon-action danger" data-confirm="¿Eliminar la categoría <?= htmlspecialchars($c['nombre']) ?>?">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<div class="modal-overlay" id="modalCategoria">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nueva categoría</h3>
            <button class="modal-close" onclick="closeModal('modalCategoria')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data" data-validate>
            <input type="hidden" name="id" id="cat_id">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="cat_nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" id="cat_descripcion" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>Icono (clase FontAwesome)</label>
                <input type="text" name="icono" id="cat_icono" class="form-control" value="fa-tag" placeholder="fa-tag, fa-cheese, fa-bottle-water">
                <small style="color:var(--admin-text-2); font-size:11px;">Ver iconos en <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a></small>
            </div>
            <div class="form-group">
                <label>URL de imagen (opcional)</label>
                <input type="text" name="imagen_url" id="cat_imagen_url" class="form-control" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>O cargar imagen desde equipo</label>
                <input type="file" name="imagen_file" class="form-control" accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin btn-admin-outline" onclick="closeModal('modalCategoria')">Cancelar</button>
                <button type="submit" class="btn-admin btn-admin-primary"><i class="fa-solid fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarCategoria(c) {
    document.getElementById('cat_id').value = c.id;
    document.getElementById('cat_nombre').value = c.nombre;
    document.getElementById('cat_descripcion').value = c.descripcion || '';
    document.getElementById('cat_icono').value = c.icono || 'fa-tag';
    document.getElementById('cat_imagen_url').value = c.imagen || '';
    document.getElementById('modalTitle').textContent = 'Editar categoría';
    openModal('modalCategoria');
}
// Reset al abrir para crear nueva
document.querySelector('button[onclick="openModal(\'modalCategoria\')"]').addEventListener('click', function() {
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_nombre').value = '';
    document.getElementById('cat_descripcion').value = '';
    document.getElementById('cat_icono').value = 'fa-tag';
    document.getElementById('cat_imagen_url').value = '';
    document.getElementById('modalTitle').textContent = 'Nueva categoría';
});
</script>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
