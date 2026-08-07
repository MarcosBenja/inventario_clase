CREATE DATABASE IF NOT EXISTS inventario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS salidas;
DROP TABLE IF EXISTS entradas;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','operador') NOT NULL DEFAULT 'operador',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    id_categoria INT NOT NULL,
    existencia_actual INT NOT NULL DEFAULT 0,
    existencia_minima INT NOT NULL DEFAULT 5,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    fecha DATE NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE salidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    fecha DATE NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO categorias (nombre, descripcion, estado) VALUES
('Electrónica', 'Dispositivos y equipos electrónicos', 1),
('Papelería', 'Artículos de oficina y escritura', 1),
('Limpieza', 'Productos de limpieza e higiene', 1),
('Herramientas', 'Herramientas y equipos de trabajo', 1);

INSERT INTO productos (codigo, nombre, descripcion, id_categoria, existencia_actual, existencia_minima, estado) VALUES
('ELEC-001', 'Teclado USB', 'Teclado inalámbrico USB compacto', 1, 10, 3, 1),
('ELEC-002', 'Mouse óptico', 'Mouse óptico USB 1200 DPI', 1, 5, 2, 1),
('ELEC-003', 'Cable HDMI', 'Cable HDMI 2m alta definición', 1, 0, 3, 1),
('PAP-001', 'Resma papel A4', 'Papel bond A4 500 hojas', 2, 20, 5, 1),
('PAP-002', 'Lapiceros azules', 'Caja de lapiceros azules x12', 2, 3, 5, 1),
('LIMP-001', 'Desinfectante', 'Desinfectante multiusos 1 litro', 3, 8, 2, 1),
('HERR-001', 'Destornilladores', 'Set de destornilladores Phillips', 4, 4, 3, 1);

INSERT INTO usuarios (nombre_completo, usuario, email, password, rol, estado) VALUES
('Marcos Benjamin Morazan Rivas', 'MARCOS', 'marcos@inventario.com', '$2y$10$uo0oMXc6NGNKXkd0smgLVubLpvkS9YYLxlhI2QDtT7cIEK13aesWm', 'admin', 1),
('Operador Sistema', 'OPERADOR', 'operador@inventario.com', '$2y$10$uo0oMXc6NGNKXkd0smgLVubLpvkS9YYLxlhI2QDtT7cIEK13aesWm', 'operador', 1);
