-- =====================================================================
-- ACTUALIZACIÓN DE BASE DE DATOS - BodegaViva v2.0
-- Añade: login de clientes, reseñas, favoritos
-- Ejecutar DESPUÉS de importar zai_tienda.sql
-- =====================================================================

USE zai_tienda;

-- ============ AÑADIR CAMPO password A clientes ============
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER email;
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS fecha_nacimiento DATE NULL;
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS ultimo_login TIMESTAMP NULL;

-- Crear contraseña para clientes existentes (admin123 hasheado)
UPDATE clientes SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMy.Mrq9B7VvBzQKZ6XhJpHj7gqMpDbeRDa' WHERE password IS NULL;

-- ============ TABLA: reseñas ============
CREATE TABLE IF NOT EXISTS resenas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    producto_id INT(11) NOT NULL,
    cliente_id INT(11) NULL,
    nombre_cliente VARCHAR(100) NOT NULL,
    calificacion TINYINT(1) NOT NULL DEFAULT 5,
    comentario TEXT,
    estado ENUM('pendiente','aprobada','rechazada') DEFAULT 'aprobada',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_producto (producto_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar reseñas de ejemplo
INSERT INTO resenas (producto_id, cliente_id, nombre_cliente, calificacion, comentario, estado) VALUES
(1, 1, 'María Quispe', 5, 'Excelente arroz, lo compro siempre. Llega fresco y a tiempo.', 'aprobada'),
(1, 2, 'Juan Pérez', 4, 'Buen producto, aunque el precio podría ser mejor.', 'aprobada'),
(5, 1, 'María Quispe', 5, 'El mejor aceite, calidad premium a buen precio.', 'aprobada'),
(5, 3, 'Lucía Ramírez', 5, 'Lo uso para cocinar todos los días, excelente.', 'aprobada'),
(16, 2, 'Juan Pérez', 5, 'Leche fresca y de buena calidad, entrega rápida.', 'aprobada'),
(16, 4, 'Carlos Mendoza', 4, 'Sabor rico, llegó bien empaquetada.', 'aprobada'),
(25, 5, 'Rosa Flores', 5, 'La Inca Kola siempre sabe igual, la mejor.', 'aprobada'),
(25, 1, 'María Quispe', 5, 'Pedí varias botellas y llegaron intactas.', 'aprobada'),
(32, 3, 'Lucía Ramírez', 5, 'A mis hijos les encantan, no fallan.', 'aprobada'),
(26, 4, 'Carlos Mendoza', 5, 'Papel suave y resistente, lo recomiendo.', 'aprobada'),
(31, 5, 'Rosa Flores', 4, 'Limpia muy bien, aunque el aroma podría ser más fuerte.', 'aprobada'),
(10, 2, 'Juan Pérez', 5, 'Atún de muy buena calidad, perfecto para ensaladas.', 'aprobada');

-- ============ TABLA: favoritos ============
CREATE TABLE IF NOT EXISTS favoritos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_fav (cliente_id, producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============ TABLA: cupones ============
CREATE TABLE IF NOT EXISTS cupones (
    id INT(11) NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    tipo ENUM('porcentaje','monto_fijo') DEFAULT 'porcentaje',
    valor DECIMAL(10,2) NOT NULL,
    minimo_compra DECIMAL(10,2) DEFAULT 0,
    usos_maximos INT(11) DEFAULT 0,
    usos_actuales INT(11) DEFAULT 0,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cupones (codigo, descripcion, tipo, valor, minimo_compra, usos_maximos, fecha_inicio, fecha_fin, estado) VALUES
('BIENVENIDA10', '10% de descuento para nuevos clientes', 'porcentaje', 10.00, 30.00, 100, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
('BODEGA20', 'S/ 20 de descuento en compras sobre S/ 100', 'monto_fijo', 20.00, 100.00, 50, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
('FREESHIP', '5% de descuento con envío gratis', 'porcentaje', 5.00, 50.00, 200, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1);

-- ============ TABLA: imagenes_producto (galería) ============
CREATE TABLE IF NOT EXISTS imagenes_producto (
    id INT(11) NOT NULL AUTO_INCREMENT,
    producto_id INT(11) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    orden INT(11) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunas imágenes de galería (usando la misma imagen principal como ejemplo)
INSERT INTO imagenes_producto (producto_id, imagen, orden) 
SELECT id, imagen, 1 FROM productos WHERE imagen IS NOT NULL AND imagen != '';

-- ============ VISTA: productos con calificación promedio ============
CREATE OR REPLACE VIEW vista_productos_calificacion AS
SELECT 
    p.*,
    c.nombre AS categoria_nombre,
    COALESCE(AVG(r.calificacion), 0) AS promedio_calificacion,
    COUNT(r.id) AS total_resenas
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN resenas r ON p.id = r.producto_id AND r.estado = 'aprobada'
GROUP BY p.id;

-- =====================================================================
-- FIN ACTUALIZACIÓN
-- =====================================================================
