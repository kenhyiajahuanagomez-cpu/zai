<?php
/**
 * BodegaViva Admin - Formulario de producto (crear / editar)
 */
// Buffer al inicio, antes que cualquier salida
if (!ob_get_level()) { ob_start(); }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// ============ PROCESAR EL FORMULARIO ANTES DE CUALQUIER HTML ============
$id = (int)($_GET['id'] ?? 0);
$producto = null;
if ($id > 0) {
    $producto = $db->fetchOne("SELECT * FROM productos WHERE id = ?", ['i', $id]);
    if (!$producto) {
        flash('error', 'Producto no encontrado.');
        header('Location: ' . SITE_URL . '/admin/productos.php');
        exit;
    }
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $marca = trim($_POST['marca'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $presentacion = trim($_POST['presentacion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $precio_anterior = !empty($_POST['precio_anterior']) ? (float)$_POST['precio_anterior'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $mas_vendido = isset($_POST['mas_vendido']) ? 1 : 0;
    $en_oferta = isset($_POST['en_oferta']) ? 1 : 0;
    $estado = isset($_POST['estado']) ? 1 : 0;
    $imagen = $_POST['imagen_url'] ?? '';

    // Mantener imagen existente si no se subió una nueva
    if ($id > 0 && empty($imagen) && !empty($producto['imagen'])) {
        $imagen = $producto['imagen'];
    }

    // Subir imagen si se cargó
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Verificar errores de subida
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir la imagen (código ' . $_FILES['imagen']['error'] . '). Puede que el archivo sea muy grande.';
        } else {
            // Validar tipo de archivo
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidas)) {
                $errores[] = 'Formato de imagen no válido. Use: JPG, PNG, WEBP o GIF.';
            } else {
                // Crear carpeta si no existe
                $carpeta = __DIR__ . '/../img/productos/';
                if (!is_dir($carpeta)) {
                    @mkdir($carpeta, 0755, true);
                }
                $nombre_archivo = uniqid('prod_') . '.' . $ext;
                $ruta_completa = $carpeta . $nombre_archivo;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
                    // Guardar ruta absoluta para que funcione desde cualquier página
                    $imagen = SITE_URL . '/img/productos/' . $nombre_archivo;
                } else {
                    $errores[] = 'No se pudo guardar la imagen. Verifique permisos de la carpeta img/productos/';
                }
            }
        }
    }

    // Validaciones
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
    if ($categoria_id === 0) $errores[] = 'Selecciona una categoría';
    if ($precio <= 0) $errores[] = 'El precio debe ser mayor a 0';

    if (empty($errores)) {
        // Sanitizar para SQL (usar htmlspecialchars ya en functions.php)
        $nombre_db = $conn->real_escape_string($nombre);
        $marca_db = $conn->real_escape_string($marca);
        $descripcion_db = $conn->real_escape_string($descripcion);
        $presentacion_db = $conn->real_escape_string($presentacion);
        $imagen_db = $conn->real_escape_string($imagen);

        if ($id > 0) {
            // ACTUALIZAR producto existente
            $stmt = $conn->prepare("UPDATE productos SET categoria_id=?, nombre=?, marca=?, descripcion=?, presentacion=?, precio=?, precio_anterior=?, stock=?, imagen=?, destacado=?, mas_vendido=?, en_oferta=?, estado=? WHERE id=?");
            if ($stmt) {
                $null_val = null;
                $stmt->bind_param('isssssdisiiiii', $categoria_id, $nombre_db, $marca_db, $descripcion_db, $presentacion_db, $precio, $precio_anterior, $stock, $imagen_db, $destacado, $mas_vendido, $en_oferta, $estado, $id);
                if ($stmt->execute()) {
                    flash('success', 'Producto actualizado correctamente.');
                } else {
                    flash('error', 'Error al actualizar: ' . $stmt->error);
                }
                $stmt->close();
            } else {
                flash('error', 'Error en consulta: ' . $conn->error);
            }
        } else {
            // CREAR nuevo producto
            $stmt = $conn->prepare("INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('isssssdisiiii', $categoria_id, $nombre_db, $marca_db, $descripcion_db, $presentacion_db, $precio, $precio_anterior, $stock, $imagen_db, $destacado, $mas_vendido, $en_oferta, $estado);
                if ($stmt->execute()) {
                    flash('success', 'Producto creado correctamente.');
                    $id = $conn->insert_id;
                } else {
                    flash('error', 'Error al crear: ' . $stmt->error);
                }
                $stmt->close();
            } else {
                flash('error', 'Error en consulta: ' . $conn->error);
            }
        }
        
        // ============ PROCESAR GALERÍA DE IMÁGENES ============
        if (!empty($_FILES['galeria']['name'][0])) {
            $carpeta_gal = __DIR__ . '/../img/productos/';
            if (!is_dir($carpeta_gal)) @mkdir($carpeta_gal, 0755, true);
            
            $total_archivos = count($_FILES['galeria']['name']);
            for ($i = 0; $i < $total_archivos; $i++) {
                if ($_FILES['galeria']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['galeria']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $nombre_arch = uniqid('gal_') . '.' . $ext;
                        if (move_uploaded_file($_FILES['galeria']['tmp_name'][$i], $carpeta_gal . $nombre_arch)) {
                            $ruta_gal = SITE_URL . '/img/productos/' . $nombre_arch;
                            $db->insert(
                                "INSERT INTO imagenes_producto (producto_id, imagen, orden) VALUES (?, ?, ?)",
                                ['isi', $id, $ruta_gal, $i + 1]
                            );
                        }
                    }
                }
            }
        }
        
        // Limpiar buffer y redirigir
        while (ob_get_level() > 0) ob_end_clean();
        header('Location: ' . SITE_URL . '/admin/productos.php');
        echo '<script>window.location.href="' . SITE_URL . '/admin/productos.php";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . SITE_URL . '/admin/productos.php"></noscript>';
        exit;
    }
}

// Eliminar imagen de galería
if (isset($_GET['eliminar_img']) && $id > 0) {
    $img_id = (int)$_GET['eliminar_img'];
    $db->execute("DELETE FROM imagenes_producto WHERE id = ? AND producto_id = ?", ['ii', $img_id, $id]);
    flash('success', 'Imagen eliminada de la galería.');
    header('Location: ' . SITE_URL . '/admin/producto_form.php?id=' . $id);
    echo '<script>window.location.href="' . SITE_URL . '/admin/producto_form.php?id=' . $id . '";</script>';
    exit;
}

// Recargar producto si hay errores (para mantener valores en el form)
if ($id > 0 && empty($producto)) {
    $producto = $db->fetchOne("SELECT * FROM productos WHERE id = ?", ['i', $id]);
}

// ============ AHORA SÍ INCLUIR EL HEADER ============
$pagina_activa = 'productos';
$pagina_titulo = 'Producto';
require_once __DIR__ . '/../includes/header_admin.php';
?>

<?php foreach (getFlash() as $f): ?>
    <div class="alert alert-<?= $f['tipo'] ?>">
        <i class="fa-solid fa-circle-<?= $f['tipo'] === 'success' ? 'check' : ($f['tipo'] === 'error' ? 'xmark' : 'info') ?>"></i>
        <?= htmlspecialchars($f['mensaje']) ?>
    </div>
<?php endforeach; ?>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <ul style="margin:0; padding-left:18px; font-size:13px;">
            <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="panel-card" style="max-width:800px;">
    <div class="panel-card-header">
        <h3><i class="fa-solid <?= $id ? 'fa-pen' : 'fa-plus' ?>"></i> <?= $id ? 'Editar' : 'Nuevo' ?> producto</h3>
        <a href="<?= SITE_URL ?>/admin/productos.php" class="btn-admin btn-admin-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>

    <form method="POST" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="id_actual" value="<?= $id ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del producto *</label>
                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto['nombre'] ?? $_POST['nombre'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Categoría *</label>
                <select name="categoria_id" class="form-control" required>
                    <option value="">Selecciona...</option>
                    <?php 
                    $categorias = obtenerCategorias();
                    foreach ($categorias as $c): 
                        $cat_sel = ($producto['categoria_id'] ?? 0) == $c['id'] ? 'selected' : '';
                        if (!$cat_sel && isset($_POST['categoria_id']) && $_POST['categoria_id'] == $c['id']) $cat_sel = 'selected';
                    ?>
                        <option value="<?= $c['id'] ?>" <?= $cat_sel ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($producto['marca'] ?? $_POST['marca'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Presentación</label>
                <input type="text" name="presentacion" class="form-control" placeholder="Ej: Bolsa 1kg, Botella 1L" value="<?= htmlspecialchars($producto['presentacion'] ?? $_POST['presentacion'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Precio (S/) *</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control" required value="<?= htmlspecialchars($producto['precio'] ?? $_POST['precio'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Precio anterior (S/) - opcional</label>
                <input type="number" step="0.01" min="0" name="precio_anterior" class="form-control" value="<?= htmlspecialchars($producto['precio_anterior'] ?? $_POST['precio_anterior'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" min="0" name="stock" class="form-control" value="<?= htmlspecialchars($producto['stock'] ?? $_POST['stock'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label>URL de imagen (opcional)</label>
                <input type="text" name="imagen_url" class="form-control" placeholder="https://... o dejar vacío si subes archivo" value="<?= htmlspecialchars($producto['imagen'] ?? $_POST['imagen_url'] ?? '') ?>">
            </div>
            <div class="form-group full">
                <label>O cargar imagen desde equipo (JPG, PNG, WEBP - máx 2MB)</label>
                <input type="file" name="imagen" class="form-control image-input" data-preview="#imgPreview" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php $img_actual = $producto['imagen'] ?? $_POST['imagen_url'] ?? ''; ?>
                <?php if (!empty($img_actual)): ?>
                    <div style="margin-top:10px;">
                        <p style="font-size:12px; color:var(--admin-text-2); margin-bottom:6px;">Imagen actual:</p>
                        <img id="imgPreview" src="<?= htmlspecialchars(normalizarImagen($img_actual)) ?>" style="width:100px; height:100px; object-fit:cover; border-radius:10px; display:block; border:2px solid var(--admin-border);">
                    </div>
                <?php else: ?>
                    <img id="imgPreview" src="" style="width:100px; height:100px; object-fit:cover; border-radius:10px; margin-top:10px; display:none;">
                <?php endif; ?>
            </div>
            <div class="form-group full">
                <label>Galería de imágenes (opcional, hasta 5 imágenes)</label>
                <input type="file" name="galeria[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="form-control">
                <small style="color:var(--admin-text-2); font-size:11px;">Puedes seleccionar varias imágenes a la vez. Se mostrarán como carrusel en la página del producto.</small>
                <?php
                // Mostrar galería actual
                if ($id > 0) {
                    $galeria = $db->fetchAll("SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY orden ASC", ['i', $id]);
                    if (!empty($galeria)):
                ?>
                    <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                        <?php foreach ($galeria as $img): ?>
                            <div style="position:relative;">
                                <img src="<?= htmlspecialchars(normalizarImagen($img['imagen'])) ?>" style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid var(--admin-border);">
                                <a href="<?= SITE_URL ?>/admin/producto_form.php?id=<?= $id ?>&eliminar_img=<?= $img['id'] ?>" 
                                   style="position:absolute; top:-6px; right:-6px; background:var(--admin-rojo); color:white; width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; text-decoration:none;" 
                                   onclick="return confirm('¿Eliminar esta imagen?')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php
                    endif;
                }
                ?>
            </div>
            <div class="form-group full">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="margin:20px 0;">
            <h4 style="font-size:14px; margin-bottom:10px; color:var(--admin-text);">Opciones del producto</h4>
            <div style="display:flex; gap:20px; flex-wrap:wrap; padding:16px; background:var(--admin-bg); border-radius:10px;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" name="destacado" <?= !empty($producto['destacado']) ? 'checked' : '' ?>> <span style="font-size:14px;">⭐ Destacado</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" name="mas_vendido" <?= !empty($producto['mas_vendido']) ? 'checked' : '' ?>> <span style="font-size:14px;">🏆 Más vendido</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" name="en_oferta" <?= !empty($producto['en_oferta']) ? 'checked' : '' ?>> <span style="font-size:14px;">🏷️ En oferta</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" name="estado" <?= (!isset($producto['estado']) || $producto['estado'] == 1) ? 'checked' : '' ?>> <span style="font-size:14px;">✅ Activo</span>
                </label>
            </div>
        </div>

        <div style="display:flex; gap:10px; padding:16px; background:var(--admin-bg); border-radius:10px; position:sticky; bottom:0;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 24px; font-size:15px;">
                <i class="fa-solid fa-save"></i> <?= $id ? 'Actualizar' : 'Crear' ?> producto
            </button>
            <a href="<?= SITE_URL ?>/admin/productos.php" class="btn-admin btn-admin-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
