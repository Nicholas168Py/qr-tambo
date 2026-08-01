<?php
/**
 * QR Tambo - Setup Script
 * Run once via browser: http://localhost/qr_tambo/setup.php
 * Creates database, tables, and default admin user
 */

require_once __DIR__ . '/config/config.php';

$results = [];
$success = true;

try {
    // Connect to the existing database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $results[] = ['step' => 'Conexión a MySQL', 'status' => 'OK', 'ok' => true];

    // Read schema file
    $schemaPath = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception('No se encontró el archivo schema.sql');
    }
    $schema = file_get_contents($schemaPath);
    $results[] = ['step' => 'Lectura de schema.sql', 'status' => 'OK', 'ok' => true];

    // Execute only CREATE TABLE statements
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($s) { return !empty($s) && stripos($s, 'CREATE TABLE') === 0; }
    );

    foreach ($statements as $stmt) {
        if (empty(trim($stmt))) continue;
        try {
            $pdo->exec($stmt . ';');
            preg_match('/(?:CREATE TABLE)\s*(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $stmt, $m);
            $desc = isset($m[0]) ? $m[0] : substr($stmt, 0, 40);
            $results[] = ['step' => "Tabla: " . ($m[1] ?? '?'), 'status' => 'Creada', 'ok' => true];
        } catch (PDOException $e) {
            if ($e->getCode() == '42S01') {
                $results[] = ['step' => "Tabla: " . ($m[1] ?? '?'), 'status' => 'Ya existía', 'ok' => true];
            } else {
                throw $e;
            }
        }
    }

    // Create default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO usuarios (cedula, nombre, password_hash, rol) VALUES (?, ?, ?, 'admin')"
    );
    $stmt->execute(['admin', 'Administrador', $adminPassword]);

    if ($stmt->rowCount() > 0) {
        $results[] = ['step' => 'Crear usuario admin', 'status' => 'Creado (cédula: admin, contraseña: admin123)', 'ok' => true];
    } else {
        $results[] = ['step' => 'Crear usuario admin', 'status' => 'Ya existía (OK)', 'ok' => true];
    }

} catch (Exception $e) {
    $results[] = ['step' => 'Error general', 'status' => $e->getMessage(), 'ok' => false];
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Tambo - Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #06080D, #0a0e18, #0d1424);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }
        h1 { text-align: center; margin-bottom: 10px; font-size: 2rem; }
        h1 span { background: linear-gradient(135deg, #6BD9F2, #07B0F2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .subtitle { text-align: center; color: rgba(255,255,255,0.6); margin-bottom: 30px; }
        .step {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            margin: 6px 0;
            background: rgba(255,255,255,0.04);
            border-radius: 10px;
            border-left: 3px solid;
            font-size: 0.9rem;
        }
        .step.ok { border-left-color: #00c853; }
        .step.error { border-left-color: #ff1744; }
        .step-name { flex: 1; font-weight: 500; }
        .step-status { color: rgba(255,255,255,0.7); font-size: 0.85rem; text-align: right; max-width: 50%; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge.ok { background: rgba(0,200,83,0.2); color: #00c853; }
        .badge.error { background: rgba(255,23,68,0.2); color: #ff1744; }
        .info-box {
            margin-top: 30px;
            padding: 20px;
            background: rgba(7,176,242,0.1);
            border: 1px solid rgba(7,176,242,0.3);
            border-radius: 12px;
            text-align: center;
        }
        .info-box h3 { color: #07C7F2; margin-bottom: 10px; }
        .info-box code {
            background: rgba(255,255,255,0.1);
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #07B0F2, #07C7F2);
            color: #06080D;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-music"></i> <span>QR Tambo</span> - Setup</h1>
        <p class="subtitle">Instalación del sistema de asistencia</p>

        <?php foreach ($results as $r): ?>
        <div class="step <?= $r['ok'] ? 'ok' : 'error' ?>">
            <span class="step-name"><?= htmlspecialchars($r['step']) ?></span>
            <span class="step-status"><?= htmlspecialchars($r['status']) ?></span>
        </div>
        <?php endforeach; ?>

        <div class="info-box">
            <h3><i class="fas fa-lock"></i> Credenciales del Administrador</h3>
            <p>Cédula: <code>admin</code></p>
            <p>Contraseña: <code>admin123</code></p>
            <br>
            <a href="index.php" class="btn">Ir al Login →</a>
        </div>
    </div>
</body>
</html>
