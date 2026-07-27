<?php
/**
 * QR Tambo - Login API
 * POST: cedula, password
 */
require_once __DIR__ . '/../../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$cedula = isset($data['cedula']) ? sanitize($data['cedula']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($cedula) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Cédula y contraseña son requeridos'], 400);
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, cedula, nombre, password_hash, rol FROM usuarios WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Cédula o contraseña incorrectos'], 401);
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['cedula'] = $user['cedula'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];

    jsonResponse([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'data' => [
            'nombre' => $user['nombre'],
            'cedula' => $user['cedula'],
            'rol' => $user['rol']
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
}
