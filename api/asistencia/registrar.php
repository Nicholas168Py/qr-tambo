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
$decodedRaw = base64_decode($token, true);
$decoded = $decodedRaw ? json_decode($decodedRaw, true) : null;

$debugInfo = '';
if ($decodedRaw === false) {
    $debugInfo = ' (base64 inválido: ' . substr($token, 0, 30) . '...)';
} elseif ($decoded === null) {
    $debugInfo = ' (JSON inválido: ' . substr($decodedRaw, 0, 50) . '...)';
} elseif (!isset($decoded['c'])) {
    $debugInfo = ' (falta campo "c". claves: ' . implode(',', array_keys($decoded)) . ')';
} elseif (!isset($decoded['f'])) {
    $debugInfo = ' (falta campo "f". claves: ' . implode(',', array_keys($decoded)) . ')';
}

if (!$decoded || !isset($decoded['c']) || !isset($decoded['f'])) {
    jsonResponse(['success' => false, 'message' => 'Código QR inválido' . $debugInfo], 400);
}

$clase = $decoded['c'];
$fecha = $decoded['f'];
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
