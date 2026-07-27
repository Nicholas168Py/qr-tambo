-- ============================================
-- QR TAMBO - Sistema de Asistencia
-- Clases de Baile
-- ============================================


-- ============================================
-- Tabla: usuarios
-- Almacena admins y bailarines
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'bailarin') NOT NULL DEFAULT 'bailarin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Tabla: clases
-- Tipos de baile disponibles
-- ============================================
CREATE TABLE IF NOT EXISTS clases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Tabla: horarios
-- Qué clase va en qué día y hora
-- dia_semana: 1=Lunes, 2=Martes, ..., 7=Domingo
-- ============================================
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clase_id INT NOT NULL,
    dia_semana TINYINT NOT NULL COMMENT '1=Lunes,2=Martes,3=Miércoles,4=Jueves,5=Viernes,6=Sábado,7=Domingo',
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Tabla: asistencia
-- Registro de asistencia de bailarines
-- Campos solicitados: Identificación, Nombre, Clase, Fecha
-- ============================================
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    clase VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora_registro TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_asistencia (cedula, clase, fecha)
) ENGINE=InnoDB;
