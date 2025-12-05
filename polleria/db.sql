-- 1. CONFIGURACIÓN INICIAL
-- Borramos la BD si existe para empezar desde cero
DROP DATABASE IF EXISTS cisne_dorado_delivery;
CREATE DATABASE cisne_dorado_delivery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cisne_dorado_delivery;

-- 2. CREACIÓN DE TABLAS
-- TABLA: usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('Administrador', 'Cliente', 'Repartidor') NOT NULL DEFAULT 'Cliente',
    estado_repartidor ENUM('Disponible', 'Ocupado', 'Inactivo') DEFAULT 'Inactivo'
);

-- TABLA: direcciones
CREATE TABLE direcciones (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    direccion_texto VARCHAR(255) NOT NULL,
    referencia VARCHAR(255),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- TABLA: categorias
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- TABLA: productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    estado ENUM('Disponible', 'Agotado') DEFAULT 'Disponible',
    id_categoria INT,
    imagen VARCHAR(255) NULL,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE SET NULL
);

-- TABLA: pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario_cliente INT NULL, 
    nombre_invitado VARCHAR(100) NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    direccion_entrega VARCHAR(255) NOT NULL,
    metodo_pago ENUM('Efectivo', 'Yape/Plin', 'Tarjeta') NOT NULL,
    monto_con_que_paga DECIMAL(10,2) NULL,
    estado ENUM('Pendiente', 'En Preparacion', 'Listo', 'En Camino', 'En Puerta', 'Entregado', 'Rechazado') DEFAULT 'Pendiente',
    id_repartidor INT NULL,
    codigo_tracking VARCHAR(50) UNIQUE,
    total DECIMAL(10, 2),
    FOREIGN KEY (id_usuario_cliente) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_repartidor) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- TABLA: detalle_pedidos
CREATE TABLE detalle_pedidos (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_producto INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
);

-- 3. POBLADO DE DATOS (SEEDERS)
-- A. CATEGORÍAS
INSERT INTO categorias (id_categoria, nombre) VALUES 
(1, 'Pollos a la Brasa'),
(2, 'Platos a la Carta'),
(3, 'Platos Especiales'),
(4, 'Innovación'),
(5, 'Caldos y Sopas');

-- B. PRODUCTOS
INSERT INTO productos (nombre, precio, estado, id_categoria, imagen) VALUES 
-- Cat 1: Pollos
('1 Pollo a la Brasa (Grande)', 54.00, 'Disponible', 1, NULL),
('1/2 Pollo a la Brasa (Grande)', 30.00, 'Disponible', 1, NULL),
('1/4 Pollo a la Brasa (Grande)', 17.00, 'Disponible', 1, NULL),
('1 Pollo + 1/4 (Presa Sola)', 66.00, 'Disponible', 1, NULL),
('1 Pollo + Chaufa', 61.00, 'Disponible', 1, NULL),
('1/2 Pollo + Chaufa', 37.00, 'Disponible', 1, NULL),
('1 Pollo + Gaseosa 1.5L', 62.00, 'Disponible', 1, NULL),
('Supermostro', 21.00, 'Disponible', 1, NULL),
('Mostrito', 17.00, 'Disponible', 1, NULL),

-- Cat 2: Carta
('Pollo Dorado', 14.00, 'Disponible', 2, NULL),
('Pollo Dorado (Parte Pecho)', 18.00, 'Disponible', 2, NULL),
('Chaufa de Pollo', 16.00, 'Disponible', 2, NULL),
('Tallarín Saltado de Pollo', 16.00, 'Disponible', 2, NULL),
('Saltado de Pollo', 16.00, 'Disponible', 2, NULL),
('Pollo a la Parrilla', 20.00, 'Disponible', 2, NULL),
('Pollo Broaster', 16.00, 'Disponible', 2, NULL),
('Pollo Broaster (Parte Pecho)', 18.00, 'Disponible', 2, NULL),
('Arroz a la Cubana', 14.00, 'Disponible', 2, NULL),
('Milanesa de Pollo', 18.00, 'Disponible', 2, NULL),

-- Cat 3: Especiales
('Pechuga a la Plancha', 20.00, 'Disponible', 3, NULL),
('Bisteck a lo Pobre', 24.00, 'Disponible', 3, NULL),
('Pechuga a lo Pobre', 24.00, 'Disponible', 3, NULL),
('Lomo a lo Cisne', 26.00, 'Disponible', 3, NULL),
('Tallarín Saltado de Carne', 18.00, 'Disponible', 3, NULL),

-- Cat 4: Innovación
('Bisteck a la Chorrillana', 18.00, 'Disponible', 4, NULL),
('Bisteck Encebollado', 18.00, 'Disponible', 4, NULL),
('Chicharrón de Pollo', 18.00, 'Disponible', 4, NULL),
('Arroz Chaufa Jorge Chavez', 22.00, 'Disponible', 4, NULL),
('Bisteck Apanado', 20.00, 'Disponible', 4, NULL),
('Aeropuerto Cubano', 19.00, 'Disponible', 4, NULL),
('Lomo Saltado', 18.00, 'Disponible', 4, NULL),

-- Cat 5: Caldos
('Caldo de Gallina (Con Presa)', 11.00, 'Disponible', 5, NULL),
('Caldo de Gallina (Sin Presa)', 9.00, 'Disponible', 5, NULL),
('Sustancia de Carne', 14.00, 'Disponible', 5, NULL),
('Sustancia de Pollo', 14.00, 'Disponible', 5, NULL),
('Sopa a la Minuta', 14.00, 'Disponible', 5, NULL),
('Dieta de Pollo', 14.00, 'Disponible', 5, NULL);

-- C. USUARIOS DEL SISTEMA
-- 1. Usuario Admin (Jeremy)
INSERT INTO usuarios (nombre, email, password, telefono, rol, estado_repartidor) 
VALUES ('Admin Prueba', 'admin@gmail.com', '123456', '987654321', 'Administrador', 'Inactivo');

-- 2. Repartidor Juan
INSERT INTO usuarios (nombre, email, password, telefono, rol, estado_repartidor) 
VALUES ('Motorizado Prueba', 'motorizado@delivery.com', '123456', '999888777', 'Repartidor', 'Disponible');

-- 3. Cliente de Prueba
INSERT INTO usuarios (nombre, email, password, telefono, rol, estado_repartidor) 
VALUES ('Cliente Prueba', 'cliente@gmail.com', '123456', '999000111', 'Cliente', 'Inactivo');
