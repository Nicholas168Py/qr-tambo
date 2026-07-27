<?php
/**
 * QR Tambo - Register API
 * POST: nombre, cedula, password
 * Creates bailarin accounts only
 */
require_once __DIR__ . '/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$nombre = isset($data['nombre']) ? sanitize($data['nombre']) : '';
$cedula = isset($data['cedula']) ? sanitize($data['cedula']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($nombre) || empty($cedula) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Todos los campos son requeridos'], 400);
}

if (strlen($password) < 4) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 4 caracteres'], 400);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if cedula already exists
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->execute([$cedula]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Esta cédula ya está registrada'], 409);
    }

    // Create user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (cedula, nombre, password_hash, rol) VALUES (?, ?, ?, 'bailarin')");
    $stmt->execute([$cedula, $nombre, $hash]);

    jsonResponse([
        'success' => true,
        'message' => 'Registro exitoso. Ya puedes iniciar sesión.'
    ], 201);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
}
