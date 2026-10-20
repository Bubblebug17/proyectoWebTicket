-- Base de datos para el sistema de tickets de sucursales Tambo
-- Instalación nueva. No inserta tickets: solo el catálogo de clasificación.
CREATE DATABASE IF NOT EXISTS ticket_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ticket_system;

-- Sucursales habilitadas para reportar tickets
CREATE TABLE IF NOT EXISTS sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_sucursales_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios de la intranet
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    celular VARCHAR(20) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL,
    tipo ENUM('responsable_tienda', 'soporte', 'responsable_area') NOT NULL,
    sucursal_id INT NOT NULL,
    area_id INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_sucursal_usuario (sucursal_id),
    UNIQUE KEY uk_usuarios_area (area_id),
    CONSTRAINT fk_usuarios_sucursal FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tipos de ticket de sucursal
CREATE TABLE IF NOT EXISTS tipos_ticket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_tipos_ticket_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Motivos concretos de cada tipo
CREATE TABLE IF NOT EXISTS subtipos_ticket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_id INT NOT NULL,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_subtipos_ticket_codigo (codigo),
    INDEX idx_subtipos_tipo (tipo_id),
    CONSTRAINT fk_subtipos_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_ticket(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tickets reportados por una sucursal
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    sucursal VARCHAR(150) NOT NULL,
    tipo_id INT NOT NULL,
    subtipo_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    archivo VARCHAR(255) NULL,
    estado ENUM('abierto', 'en_proceso', 'cerrado') NOT NULL DEFAULT 'abierto',
    soporte_id INT NULL,
    encargado_id INT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tickets_soporte FOREIGN KEY (soporte_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_encargado FOREIGN KEY (encargado_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_ticket(id),
    CONSTRAINT fk_tickets_subtipo FOREIGN KEY (subtipo_id) REFERENCES subtipos_ticket(id),
    INDEX idx_soporte (soporte_id),
    INDEX idx_encargado (encargado_id),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_id),
    INDEX idx_subtipo (subtipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Respuestas del equipo de atención
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensajes_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensajes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sucursales (codigo, nombre, orden) VALUES
('TB-MIR', 'Tambo Miraflores', 1),
('TB-SBO', 'Tambo San Borja', 2),
('TB-OLI', 'Tambo Los Olivos', 3);

-- Usuarios de intranet. La contraseña de los tres es "password".
INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id)
SELECT 'María', 'Quispe', 'tienda@demo.com', '999111222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_tienda', id
FROM sucursales WHERE codigo = 'TB-MIR';

INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id)
SELECT 'Carlos', 'Mendoza', 'soporte@demo.com', '999333444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'soporte', id
FROM sucursales WHERE codigo = 'TB-SBO';

INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id)
SELECT 'Lucía', 'Ramos', 'area@demo.com', '999555666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_area', id
FROM sucursales WHERE codigo = 'TB-OLI';

-- Catálogo de clasificación (no son tickets)
INSERT INTO tipos_ticket (codigo, nombre, orden) VALUES
('logistico', 'Logístico', 1),
('tecnologico', 'Tecnológico', 2),
('administrativo', 'Administrativo', 3),
('otros', 'Otros', 4);

INSERT INTO subtipos_ticket (tipo_id, codigo, nombre, orden)
SELECT t.id, s.codigo, s.nombre, s.orden
FROM tipos_ticket t
JOIN (
    SELECT 'logistico' AS tipo, 'falta_stock_lider' AS codigo, 'Falta de stock en productos líder' AS nombre, 1 AS orden
    UNION ALL SELECT 'logistico', 'lote_vencido', 'Lote vencido', 2
    UNION ALL SELECT 'logistico', 'falta_uso_diario', 'Falta de elementos de uso diario', 3
    UNION ALL SELECT 'logistico', 'insumos_sanitarios', 'Reabastecimiento de insumos sanitarios', 4
    UNION ALL SELECT 'logistico', 'pedido_no_recepcionado', 'Pedido no recepcionado', 5
    UNION ALL SELECT 'logistico', 'diferencia_inventario', 'Diferencia de inventario', 6
    UNION ALL SELECT 'tecnologico', 'falla_pos', 'Falla del punto de venta (POS)', 1
    UNION ALL SELECT 'tecnologico', 'impresora_tickets', 'Impresora de tickets sin funcionar', 2
    UNION ALL SELECT 'tecnologico', 'sin_conexion', 'Sin conexión de red o internet', 3
    UNION ALL SELECT 'tecnologico', 'sistema_inventario', 'Sistema de inventario no disponible', 4
    UNION ALL SELECT 'tecnologico', 'lector_barras', 'Lector de código de barras defectuoso', 5
    UNION ALL SELECT 'tecnologico', 'equipo_frio', 'Falla en equipo de frío o refrigeración', 6
    UNION ALL SELECT 'administrativo', 'error_cierre_caja', 'Error en cierre de caja', 1
    UNION ALL SELECT 'administrativo', 'diferencia_arqueo', 'Diferencia de arqueo', 2
    UNION ALL SELECT 'administrativo', 'cambio_turno', 'Solicitud de cambio de turno', 3
    UNION ALL SELECT 'administrativo', 'incidencia_personal', 'Incidencia de personal', 4
    UNION ALL SELECT 'administrativo', 'documentacion_incompleta', 'Documentación de guía o factura incompleta', 5
    UNION ALL SELECT 'administrativo', 'reclamo_cliente', 'Reclamo de cliente pendiente de gestión', 6
    UNION ALL SELECT 'otros', 'incidente_seguridad', 'Incidente de seguridad en tienda', 1
    UNION ALL SELECT 'otros', 'dano_infraestructura', 'Daño de infraestructura del local', 2
    UNION ALL SELECT 'otros', 'servicios_basicos', 'Falla de servicios básicos (agua o luz)', 3
    UNION ALL SELECT 'otros', 'mantenimiento_local', 'Limpieza o mantenimiento del local', 4
    UNION ALL SELECT 'otros', 'consulta_no_clasificada', 'Consulta no clasificada', 5
    UNION ALL SELECT 'otros', 'sugerencia_mejora', 'Sugerencia de mejora operativa', 6
) s ON s.tipo = t.codigo;

ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuarios_area FOREIGN KEY (area_id) REFERENCES tipos_ticket(id);

-- Un encargado de área por cada tipo. Contraseña de todos los usuarios demo: password
UPDATE usuarios u
INNER JOIN tipos_ticket t ON t.codigo = 'logistico'
SET u.area_id = t.id
WHERE u.email = 'area@demo.com';

INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id, area_id)
SELECT 'Andrés', 'Paredes', 'tecnologia@demo.com', '999555001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_area', s.id, t.id
FROM sucursales s
INNER JOIN tipos_ticket t ON t.codigo = 'tecnologico'
WHERE s.codigo = 'TB-SBO';

INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id, area_id)
SELECT 'Rosa', 'Delgado', 'administracion@demo.com', '999555002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_area', s.id, t.id
FROM sucursales s
INNER JOIN tipos_ticket t ON t.codigo = 'administrativo'
WHERE s.codigo = 'TB-SBO';

INSERT INTO usuarios (nombre, apellido, email, celular, password, tipo, sucursal_id, area_id)
SELECT 'Javier', 'Soto', 'otros@demo.com', '999555003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_area', s.id, t.id
FROM sucursales s
INNER JOIN tipos_ticket t ON t.codigo = 'otros'
WHERE s.codigo = 'TB-SBO';

-- No se insertan tickets de ejemplo.
