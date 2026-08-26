<?php
/**
 * Crea la tabla de sesiones si no existe.
 * Subir una vez a producción y acceder una vez, o ejecutar manualmente.
 */

require __DIR__ . '/../config/init.php';

use Support\Database;

$db = Database::getInstance()->getConnection();

$db->exec("CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    data TEXT NOT NULL,
    last_access DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "Tabla 'sessions' creada/verificada correctamente.\n";
