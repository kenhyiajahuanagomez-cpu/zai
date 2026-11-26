-- =====================================================================
-- BASE DE DATOS: zai_tienda
-- Proyecto: BodegaViva - Tienda de Abarrotes
-- Motor: MySQL / MariaDB (compatible con XAMPP / phpMyAdmin)
-- =====================================================================

-- Eliminar la base de datos si ya existe (opcional, para reinstalación limpia)
DROP DATABASE IF EXISTS zai_tienda;
CREATE DATABASE zai_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zai_tienda;

-- =====================================================================
-- TABLA: usuarios (administradores del sistema)
-- =====================================================================
CREATE TABLE usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','super_admin') DEFAULT 'admin',
    estado TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario por defecto: admin@bodegaviva.pe / admin123
-- Password hasheado con PASSWORD_DEFAULT (bcrypt) de PHP
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador BodegaViva', 'admin@bodegaviva.pe', '$2y$10$wH5b6pK2yN8oQ4vX7zR1uO3aY9jF0mC5kL2dJ8sI1tE6rV9wU0pMq', 'super_admin'),
('Vendedor Bodega', 'vendedor@zai.pe', '$2y$10$wH5b6pK2yN8oQ4vX7zR1uO3aY9jF0mC5kL2dJ8sI1tE6rV9wU0pMq', 'admin');

-- NOTA: El hash anterior es un placeholder. El sistema PHP regenerará
-- el hash correcto en el primer login (ver includes/auth.php).
-- Usuario inicial recomendado: admin@bodegaviva.pe / admin123

-- =====================================================================
-- TABLA: clientes
-- =====================================================================
CREATE TABLE clientes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    direccion VARCHAR(255),
    referencia VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_celular (celular)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO clientes (nombre, celular, email, direccion, referencia) VALUES
('María Quispe', '987654321', 'maria.q@gmail.com', 'Av. Los Olivos 123', 'Frente al parque'),
('Juan Pérez', '987123456', 'juan.perez@gmail.com', 'Jr. Huánuco 456', 'A lado de la farmacia'),
('Lucía Ramírez', '956789123', 'lucia.r@hotmail.com', 'Calle Las Palmeras 789', 'Esquina con el grifo'),
('Carlos Mendoza', '999887766', 'carlos.m@gmail.com', 'Av. Túpac Amaru 321', 'Cerca al mercado'),
('Rosa Flores', '945678912', 'rosa.f@yahoo.com', 'Jr. Arequipa 654', 'Segundo piso');

-- =====================================================================
-- TABLA: categorias
-- =====================================================================
CREATE TABLE categorias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50),
    imagen VARCHAR(255),
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nombre, descripcion, icono, imagen) VALUES
('Alimentos Básicos', 'Arroz, fideos, azúcar, aceites y abarrotes secos', 'fa-wheat-awn', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80'),
('Lácteos y Refrigerados', 'Leche, yogur, quesos, huevos y embutidos', 'fa-cheese', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=600&q=80'),
('Bebidas', 'Gaseosas, aguas, jugos y néctares', 'fa-bottle-water', 'https://images.unsplash.com/photo-1543007630-9710e4a00a20?w=600&q=80'),
('Golosinas y Panadería', 'Galletas, chocolates, snacks y panes', 'fa-cookie-bite', 'https://images.unsplash.com/photo-1598373182133-52452f7691ef?w=600&q=80'),
('Limpieza y Aseo', 'Detergentes, jabones, papel higiénico y limpieza', 'fa-pump-soap', 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=600&q=80');

-- =====================================================================
-- TABLA: productos
-- =====================================================================
CREATE TABLE productos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    categoria_id INT(11) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    marca VARCHAR(100),
    descripcion TEXT,
    presentacion VARCHAR(100),
    precio DECIMAL(10,2) NOT NULL,
    precio_anterior DECIMAL(10,2) DEFAULT NULL,
    stock INT(11) DEFAULT 0,
    imagen VARCHAR(255),
    destacado TINYINT(1) DEFAULT 0,
    mas_vendido TINYINT(1) DEFAULT 0,
    en_oferta TINYINT(1) DEFAULT 0,
    vendidos INT(11) DEFAULT 0,
    estado TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
    INDEX idx_categoria (categoria_id),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- CATEGORÍA 1: Alimentos Básicos ----------
INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, vendidos) VALUES
(1, 'Arroz Costeño', 'Costeño', 'Arroz blanco de grano largo, ideal para el consumo diario.', 'Saco 5kg', 22.50, 25.00, 80, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&q=80', 1, 1, 1, 320),
(1, 'Fideos Don Vittorio', 'Don Vittorio', 'Fideos tipo espagueti de sémola de trigo.', 'Paquete 500g', 3.20, NULL, 150, 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=500&q=80', 1, 1, 0, 215),
(1, 'Azúcar Rubia', 'Laredo', 'Azúcar rubia de caña, perfecta para postres y bebidas.', 'Bolsa 1kg', 4.80, 5.50, 100, 'https://images.unsplash.com/photo-1610725664285-7c57e6eeac3f?w=500&q=80', 0, 0, 1, 89),
(1, 'Sal Marina', 'Sal Mar', 'Sal marina refinada yodada.', 'Bolsa 500g', 1.50, NULL, 200, 'https://images.unsplash.com/photo-1518110925495-b37653e4f39f?w=500&q=80', 0, 0, 0, 60),
(1, 'Aceite Vegetal Primor', 'Primor', 'Aceite vegetal de soya para cocinar.', 'Botella 1L', 12.90, 14.50, 90, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500&q=80', 1, 1, 1, 178),
(1, 'Lentejas', 'Madre Luna', 'Lentejas seleccionadas, ricas en proteínas.', 'Bolsa 500g', 3.50, NULL, 70, 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?w=500&q=80', 0, 0, 0, 42),
(1, 'Frijoles Canario', 'Madre Luna', 'Frijol canario peruano de primera calidad.', 'Bolsa 500g', 4.20, NULL, 65, 'https://images.unsplash.com/photo-1515543666-5a37c8d7e5c1?w=500&q=80', 0, 0, 0, 38),
(1, 'Garbanzos', 'Madre Luna', 'Garbanzos seleccionados para preparaciones diversas.', 'Bolsa 500g', 4.00, NULL, 55, 'https://images.unsplash.com/photo-1566474108317-31f1b6b3e3c1?w=500&q=80', 0, 0, 0, 31),
(1, 'Pallares', 'Madre Luna', 'Pallares blancos, típicos de la cocina peruana.', 'Bolsa 500g', 5.50, NULL, 40, 'https://images.unsplash.com/photo-1605000797499-95a51c5269ae?w=500&q=80', 0, 0, 0, 25),
(1, 'Atún Florida', 'Florida', 'Atún en trozos en aceite vegetal.', 'Lata 170g', 6.50, 7.50, 110, 'https://images.unsplash.com/photo-1599487488170-d11ec9271729?w=500&q=80', 1, 1, 1, 156),
(1, 'Harina Preparada', 'Blanca Flor', 'Harina de trigo con levadura para panificación.', 'Bolsa 1kg', 5.20, NULL, 95, 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=500&q=80', 0, 0, 0, 67),
(1, 'Avena Quaker', 'Quaker', 'Avena en hojuelas para un desayuno nutritivo.', 'Paquete 400g', 4.90, 5.80, 120, 'https://images.unsplash.com/photo-1517686469429-8bdb88b9f907?w=500&q=80', 1, 0, 1, 134),
(1, 'Café Altomayo', 'Altomayo', 'Café tostado molido tradicional peruano.', 'Paquete 250g', 11.90, NULL, 75, 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=500&q=80', 0, 1, 0, 98),
(1, 'Té Hielero', 'Hielero', 'Caja de té en sobres individuales.', 'Caja 25 sobres', 3.80, NULL, 85, 'https://images.unsplash.com/photo-1597318181409-cf64d0b5d8a2?w=500&q=80', 0, 0, 0, 52);

-- ---------- CATEGORÍA 2: Lácteos y Refrigerados ----------
INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, vendidos) VALUES
(2, 'Leche Gloria Evaporada', 'Gloria', 'Leche evaporada entera, ideal para preparar postres.', 'Lata 410g', 4.50, NULL, 180, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500&q=80', 1, 1, 0, 280),
(2, 'Leche Gloria Entera', 'Gloria', 'Leche fresca entera ultrapasteurizada.', 'Caja 1L', 5.40, 6.00, 140, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&q=80', 1, 1, 1, 195),
(2, 'Yogur Gloria', 'Gloria', 'Yogur de frutilla con vitaminas.', 'Pack 4x150ml', 7.80, NULL, 90, 'https://images.unsplash.com/photo-1571212515416-fef09c0050b5?w=500&q=80', 0, 0, 0, 76),
(2, 'Mantequilla Laive', 'Laive', 'Mantequilla sin sal, cremosa.', 'Barra 100g', 5.90, NULL, 60, 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=500&q=80', 0, 0, 0, 48),
(2, 'Margarina Dorada', 'Dorada', 'Margarina vegetal para untar y cocinar.', 'Barra 200g', 4.30, 4.90, 75, 'https://images.unsplash.com/photo-1594989611393-09c5dc6f5c8e?w=500&q=80', 0, 0, 1, 41),
(2, 'Huevos de Campo', 'Granja', 'Huevos frescos de gallinas criadas en campo.', 'Docena', 8.50, 9.50, 50, 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=500&q=80', 1, 1, 1, 167),
(2, 'Queso Edam', 'Laive', 'Queso fresco tipo Edam en porción.', 'Bloque 250g', 11.50, NULL, 45, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=500&q=80', 0, 0, 0, 35),
(2, 'Hot Dogs Razzies', 'Razzies', 'Salchichas de carne y pollo.', 'Paquete 500g', 8.90, NULL, 70, 'https://images.unsplash.com/photo-1612392061787-2d078b3e573f?w=500&q=80', 0, 1, 0, 88),
(2, 'Jamonada Piurana', 'Piurana', 'Jamonada delgada lista para consumir.', 'Paquete 250g', 9.50, 10.50, 55, 'https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=500&q=80', 0, 0, 1, 38);

-- ---------- CATEGORÍA 3: Bebidas ----------
INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, vendidos) VALUES
(3, 'Inca Kola', 'Lindley', 'La bebida nacional del Perú, sabor único.', 'Botella 1.5L', 7.50, 8.50, 200, 'https://images.unsplash.com/photo-1622483767028-3f66d3b0ab1f?w=500&q=80', 1, 1, 1, 410),
(3, 'Coca-Cola', 'Coca-Cola', 'Gaseosa refrescante de cola.', 'Botella 1.5L', 7.80, NULL, 180, 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=500&q=80', 1, 1, 0, 365),
(3, 'Kola Real', 'Aje', 'Gaseosa sabor cola a un precio accesible.', 'Botella 1.5L', 5.50, 6.50, 150, 'https://images.unsplash.com/photo-1592597143078-3e6d28a06d7b?w=500&q=80', 0, 0, 1, 142),
(3, 'Agua Mineral San Luis', 'San Luis', 'Agua mineral sin gas natural.', 'Botella 1L', 2.50, NULL, 250, 'https://images.unsplash.com/photo-1548837555-4c6f9b6b5d2b?w=500&q=80', 0, 1, 0, 198),
(3, 'Jugo Frugos', 'Frugos', 'Jugo de frutas en presentación personal.', 'Caja 1L', 5.90, NULL, 110, 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=500&q=80', 0, 0, 0, 87),
(3, 'Néctar Pulpi', 'Pulpi', 'Néctar de durazno con pulpa de fruta.', 'Caja 1L', 5.40, 6.20, 95, 'https://images.unsplash.com/photo-1571547575207-f7f43c3f7d7c?w=500&q=80', 0, 0, 1, 64);

-- ---------- CATEGORÍA 4: Golosinas y Panadería ----------
INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, vendidos) VALUES
(4, 'Galletas Soda Field', 'Field', 'Galletas saladas crujientes.', 'Paquete 200g', 2.50, NULL, 220, 'https://images.unsplash.com/photo-1577569548258-ff2a34f7dd2d?w=500&q=80', 0, 1, 0, 145),
(4, 'Chocolate Sublime', 'Sublime', 'Chocolate con maní tradicional peruano.', 'Barra 40g', 2.80, 3.20, 180, 'https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?w=500&q=80', 1, 1, 1, 256),
(4, 'Caramelos de Frutas', 'Coflusa', 'Bolsa de caramelos surtidos.', 'Bolsa 100g', 1.80, NULL, 150, 'https://images.unsplash.com/photo-1598934666237-7e9c0a2e3b3e?w=500&q=80', 0, 0, 0, 67),
(4, 'Chicles Trident', 'Trident', 'Goma de mascar sin azúcar.', 'Paquete 10 u', 4.50, NULL, 130, 'https://images.unsplash.com/photo-1623243173472-bbf3c8c5e0a5?w=500&q=80', 0, 0, 0, 53),
(4, 'Papas Fritas Lay''s', 'Lay''s', 'Papas fritas crujientes sabor original.', 'Bolsa 170g', 6.90, 7.90, 160, 'https://images.unsplash.com/photo-1613919113640-25732ec5e61f?w=500&q=80', 1, 1, 1, 178),
(4, 'Maní Salado', 'Maní', 'Maní tostado y salado.', 'Bolsa 250g', 4.80, NULL, 90, 'https://images.unsplash.com/photo-1599909533321-94a8c4d8d3e1?w=500&q=80', 0, 0, 0, 71),
(4, 'Pan Francés', 'Panadería', 'Pan francés fresco del día.', 'Unidad', 0.30, NULL, 300, 'https://images.unsplash.com/photo-1568254183919-78a4f43a2877?w=500&q=80', 1, 1, 0, 580),
(4, 'Pan de Yema', 'Panadería', 'Pan dulce de yema esponjoso.', 'Unidad', 1.20, NULL, 120, 'https://images.unsplash.com/photo-1598373182133-52452f7691ef?w=500&q=80', 0, 0, 0, 89);

-- ---------- CATEGORÍA 5: Limpieza y Aseo ----------
INSERT INTO productos (categoria_id, nombre, marca, descripcion, presentacion, precio, precio_anterior, stock, imagen, destacado, mas_vendido, en_oferta, vendidos) VALUES
(5, 'Detergente Bolívar', 'Bolívar', 'Detergente en polro para ropa.', 'Bolsa 1kg', 12.50, 14.00, 100, 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=500&q=80', 1, 1, 1, 134),
(5, 'Jabón Opal', 'Opal', 'Jabón para lavar ropa a mano.', 'Paquete 3x250g', 7.90, NULL, 90, 'https://images.unsplash.com/photo-1602693258838-4a1e4a5c3e3a?w=500&q=80', 0, 0, 0, 65),
(5, 'Papel Higiénico Elite', 'Elite', 'Papel higiénico de doble hoja suave.', 'Pack 4 rollos', 8.50, 9.50, 110, 'https://images.unsplash.com/photo-1583268500712-0e0a1f2f1c1d?w=500&q=80', 1, 1, 1, 187),
(5, 'Pasta Dental Dental', 'Dental', 'Pasta dental con flúor.', 'Tubo 100ml', 4.90, NULL, 130, 'https://images.unsplash.com/photo-1559591934-3d8c1b9d1e7e?w=500&q=80', 0, 0, 0, 78),
(5, 'Jabón de Tocador', 'Camay', 'Jabón en barra para piel delicada.', 'Caja 3x100g', 6.80, 7.50, 85, 'https://images.unsplash.com/photo-1600857544200-b2f666a9a2ec?w=500&q=80', 0, 0, 1, 56),
(5, 'Lejía Clorox', 'Clorox', 'Lejía desinfectante para el hogar.', 'Botella 1L', 5.20, NULL, 95, 'https://images.unsplash.com/photo-1583947215259-38e31be875f5?w=500&q=80', 0, 0, 0, 72),
(5, 'Limpiador Multiusos', 'Pato', 'Limpiador para superficies varias del hogar.', 'Botella 1L', 6.50, 7.20, 70, 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=500&q=80', 0, 0, 1, 48);

-- =====================================================================
-- TABLA: pedidos
-- =====================================================================
CREATE TABLE pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11),
    nombre_cliente VARCHAR(100) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    referencia VARCHAR(255),
    metodo_pago ENUM('efectivo','yape','plin','transferencia') DEFAULT 'efectivo',
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente','procesando','enviado','entregado','cancelado') DEFAULT 'pendiente',
    observaciones TEXT,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pedidos (cliente_id, nombre_cliente, celular, direccion, referencia, metodo_pago, total, estado, fecha_pedido) VALUES
(1, 'María Quispe', '987654321', 'Av. Los Olivos 123', 'Frente al parque', 'yape', 45.80, 'entregado', '2026-10-28 10:30:00'),
(2, 'Juan Pérez', '987123456', 'Jr. Huánuco 456', 'A lado de la farmacia', 'efectivo', 78.40, 'entregado', '2026-10-29 14:15:00'),
(3, 'Lucía Ramírez', '956789123', 'Calle Las Palmeras 789', 'Esquina con el grifo', 'plin', 32.50, 'enviado', '2026-11-05 09:20:00'),
(4, 'Carlos Mendoza', '999887766', 'Av. Túpac Amaru 321', 'Cerca al mercado', 'yape', 56.90, 'procesando', '2026-11-06 16:45:00'),
(5, 'Rosa Flores', '945678912', 'Jr. Arequipa 654', 'Segundo piso', 'efectivo', 28.30, 'pendiente', '2026-11-07 08:10:00'),
(NULL, 'Pedro Salazar', '988776655', 'Av. Brasil 890', 'Frente al banco', 'transferencia', 89.70, 'entregado', '2026-11-01 11:00:00'),
(NULL, 'Ana Torres', '977665544', 'Jr. Cusco 234', 'Casa azul', 'yape', 42.10, 'entregado', '2026-11-03 13:30:00'),
(NULL, 'Luis Gómez', '966554433', 'Av. Primavera 567', 'Edificio B dpto 301', 'efectivo', 65.20, 'entregado', '2026-11-04 17:50:00');

-- =====================================================================
-- TABLA: detalle_pedidos
-- =====================================================================
CREATE TABLE detalle_pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pedido_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    cantidad INT(11) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 'Arroz Costeño', 1, 22.50, 22.50),
(1, 5, 'Aceite Vegetal Primor', 1, 12.90, 12.90),
(1, 16, 'Leche Gloria Entera', 2, 5.40, 10.80),
(2, 19, 'Huevos de Campo', 2, 8.50, 17.00),
(2, 22, 'Hot Dogs Razzies', 2, 8.90, 17.80),
(2, 25, 'Inca Kola', 3, 7.50, 22.50),
(2, 18, 'Jamonada Piurana', 2, 9.50, 19.00),
(3, 32, 'Papas Fritas Lay''s', 2, 6.90, 13.80),
(3, 9, 'Atún Florida', 2, 6.50, 13.00),
(3, 1, 'Arroz Costeño', 1, 22.50, 22.50),
(4, 26, 'Papel Higiénico Elite', 2, 8.50, 17.00),
(4, 31, 'Detergente Bolívar', 1, 12.50, 12.50),
(4, 16, 'Leche Gloria Entera', 2, 5.40, 10.80),
(4, 25, 'Inca Kola', 2, 7.50, 15.00),
(5, 32, 'Papas Fritas Lay''s', 1, 6.90, 6.90),
(5, 23, 'Queso Edam', 1, 11.50, 11.50),
(5, 13, 'Café Altomayo', 1, 11.90, 11.90),
(6, 1, 'Arroz Costeño', 2, 22.50, 45.00),
(6, 5, 'Aceite Vegetal Primor', 2, 12.90, 25.80),
(6, 32, 'Papas Fritas Lay''s', 2, 6.90, 13.80),
(7, 25, 'Inca Kola', 2, 7.50, 15.00),
(7, 26, 'Papel Higiénico Elite', 1, 8.50, 8.50),
(7, 32, 'Papas Fritas Lay''s', 1, 6.90, 6.90),
(8, 1, 'Arroz Costeño', 1, 22.50, 22.50),
(8, 25, 'Inca Kola', 2, 7.50, 15.00),
(8, 26, 'Papel Higiénico Elite', 1, 8.50, 8.50),
(8, 5, 'Aceite Vegetal Primor', 1, 12.90, 12.90);

-- =====================================================================
-- TABLA: ventas (resumen de ventas para reportes)
-- =====================================================================
CREATE TABLE ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pedido_id INT(11),
    fecha_venta DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50),
    PRIMARY KEY (id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
    INDEX idx_fecha (fecha_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO ventas (pedido_id, fecha_venta, monto, metodo_pago) VALUES
(1, '2026-10-28', 45.80, 'yape'),
(2, '2026-10-29', 78.40, 'efectivo'),
(6, '2026-11-01', 89.70, 'transferencia'),
(7, '2026-11-03', 42.10, 'yape'),
(8, '2026-11-04', 65.20, 'efectivo');

-- =====================================================================
-- VISTA: vista_productos_completos (para consultas rápidas)
-- =====================================================================
CREATE OR REPLACE VIEW vista_productos_completos AS
SELECT 
    p.id,
    p.nombre,
    p.marca,
    p.descripcion,
    p.presentacion,
    p.precio,
    p.precio_anterior,
    p.stock,
    p.imagen,
    p.destacado,
    p.mas_vendido,
    p.en_oferta,
    p.vendidos,
    p.estado,
    c.nombre AS categoria_nombre
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id;

-- =====================================================================
-- FIN DEL ARCHIVO
-- =====================================================================
