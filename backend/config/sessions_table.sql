-- Tabla de sesiones para reemplazar archivos de sesión de PHP
-- Ejecutar una sola vez en la base de datos
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    data TEXT NOT NULL,
    last_access DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Limpiar sesiones viejas (opcional, ejecutar periódicamente)
-- DELETE FROM sessions WHERE last_access < DATE_SUB(NOW(), INTERVAL 30 DAY);
