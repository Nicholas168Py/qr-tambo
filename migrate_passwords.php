<?php
/**
 * QR Tambo - Migrate plain-text passwords to bcrypt hashes
 * 
 * Run ONCE from browser after uploading. Delete after use.
 * Safe to re-run: already-hashed passwords are skipped.
 */
require_once __DIR__ . '/config/init.php';

// Optional: protect with admin session
if (!isLoggedIn() || $_SESSION['rol'] !== 'admin') {
    die('Acceso denegado. Debes ser admin.');
}

echo "<h2>Migrando contraseñas...</h2><pre>";

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("SELECT id, cedula, nombre, password_hash FROM usuarios");
    $count = 0;
    
    while ($row = $stmt->fetch()) {
        $hash = $row['password_hash'];
        
        // Skip if already a bcrypt hash ($2y$...)
        if (strlen($hash) >= 60 && strpos($hash, '$2y$') === 0) {
            echo "✓ {$row['cedula']} ({$row['nombre']}) — ya hasheada<br>";
            continue;
        }
        
        // Hash the plain-text password
        $newHash = password_hash($hash, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $update->execute([$newHash, $row['id']]);
        
        echo "→ {$row['cedula']} ({$row['nombre']}) — migrada<br>";
        $count++;
    }
    
    echo "<br><strong>Migración completada. {$count} contraseña(s) actualizada(s).</strong>";
    echo "<br><br>⚠️ <strong>Eliminá este archivo del servidor después de usarlo.</strong>";
    
} catch (Exception $e) {
    echo "<strong>Error:</strong> " . $e->getMessage();
}
