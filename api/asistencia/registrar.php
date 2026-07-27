<?php
/**
 * QR Tambo - Register Attendance API
 * POST: token (base64 encoded JSON from QR)
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$token = isset($data['token']) ? $data['token'] : '';

if (empty($token)) {
    jsonResponse(['success' => false, 'message' => 'Token QR requerido'], 400);
}

// Decode the QR token
$decoded = json_decode(base64_decode($token), true);

if (!$decoded || !isset($decoded['clase']) || !isset($decoded['fecha'])) {
    jsonResponse(['success' => false, 'message' => 'Código QR inválido'], 400);
}

$clase = $decoded['clase'];
$fecha = $decoded['fecha'];
$user = getCurrentUser();

// Validate date (allow today and yesterday for flexibility)
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

if ($fecha !== $today && $fecha !== $yesterday) {
    jsonResponse(['success' => false, 'message' => 'Este código QR ha expirado. Solo es válido para el día de la clase.'], 400);
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if already registered
    $stmt = $db->prepare("SELECT id FROM asistencia WHERE cedula = ? AND clase = ? AND fecha = ?");
    $stmt->execute([$user['cedula'], $clase, $fecha]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ya registraste tu asistencia a esta clase hoy'], 409);
    }

    // Register attendance
    $horaRegistro = date('H:i:s');
    $stmt = $db->prepare(
        "INSERT INTO asistencia (cedula, nombre, clase, fecha, hora_registro) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$user['cedula'], $user['nombre'], $clase, $fecha, $horaRegistro]);

    jsonResponse([
        'success' => true,
        'message' => '¡Asistencia registrada exitosamente!',
        'data' => [
            'nombre' => $user['nombre'],
            'clase' => $clase,
            'fecha' => $fecha,
            'hora_registro' => $horaRegistro
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al registrar asistencia'], 500);
}
