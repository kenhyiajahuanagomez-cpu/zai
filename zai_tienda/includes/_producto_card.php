<?php
/**
 * Partial: Tarjeta de producto
 * Variable $p debe contener: id, nombre, marca, presentacion, precio, precio_anterior, stock, imagen, categoria_nombre, en_oferta, destacado, mas_vendido, vendidos
 */
$descuento = calcularDescuento($p['precio'], $p['precio_anterior']);
$stock = (int)$p['stock'];
$stock_class = '';
$stock_text = 'En stock';
if ($stock === 0) {
    $stock_class = 'agotado';
    $stock_text = 'Agotado';
} elseif ($stock < 10) {
    $stock_class = 'stock-bajo';
    $stock_text = '¡Solo ' . $stock . ' disponibles!';
}
?>
<div class="producto-card reveal" 
     data-id="<?= $p['id'] ?>"
     data-precio="<?= $p['precio'] ?>"
     data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
     data-categoria="<?= htmlspecialchars($p['categoria_nombre'] ?? '') ?>"
     data-vendidos="<?= $p['vendidos'] ?? 0 ?>">
    <div class="producto-img">
        <a href="<?= SITE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>" style="display:block; width:100%; height:100%;">
            <div class="producto-badges">
                <?php if ($p['en_oferta'] && $descuento > 0): ?>
                    <span class="badge badge-oferta">-<?= $descuento ?>%</span>
                <?php endif; ?>
                <?php if ($p['destacado'] ?? 0): ?>
                    <span class="badge badge-destacado"><i class="fa-solid fa-star"></i> Top</span>
                <?php endif; ?>
                <?php if (($p['mas_vendido'] ?? 0) && !($p['en_oferta'] ?? 0)): ?>
                    <span class="badge badge-nuevo">Más vendido</span>
                <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars(normalizarImagen($p['imagen'])) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy"
                 onerror="this.src='https://via.placeholder.com/400x400/0E5A3C/FFFFFF?text=<?= urlencode($p['nombre']) ?>'">
        </a>
        <button class="producto-fav" aria-label="Favorito"><i class="fa-regular fa-heart"></i></button>
    </div>
    <div class="producto-info">
        <a href="<?= SITE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
            <span class="producto-categoria"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></span>
            <div class="producto-marca"><?= htmlspecialchars($p['marca'] ?? '') ?></div>
            <h3 class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></h3>
            <div class="producto-presentacion"><?= htmlspecialchars($p['presentacion'] ?? '') ?></div>
            <div class="producto-precios">
                <span class="precio-actual"><?= formatPrecio($p['precio']) ?></span>
                <?php if ($p['precio_anterior'] && $p['precio_anterior'] > $p['precio']): ?>
                    <span class="precio-anterior"><?= formatPrecio($p['precio_anterior']) ?></span>
                <?php endif; ?>
            </div>
            <div class="producto-disponibilidad <?= $stock_class ?>">
                <i class="fa-solid <?= $stock === 0 ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
                <?= $stock_text ?>
            </div>
        </a>
        <div class="producto-acciones">
            <?php if ($stock > 0): ?>
                <button class="btn-add-cart" data-id="<?= $p['id'] ?>" <?= $stock === 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-cart-plus"></i> Agregar al carrito
                </button>
            <?php else: ?>
                <button class="btn-add-cart" disabled style="background:var(--gris-texto); cursor:not-allowed;">
                    <i class="fa-solid fa-ban"></i> Agotado
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
