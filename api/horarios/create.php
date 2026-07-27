<?php
/**
 * QR Tambo - Create Horario API
 * POST: clase_id, dia_semana, hora_inicio, hora_fin
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$clase_id = isset($data['clase_id']) ? intval($data['clase_id']) : 0;
$dia_semana = isset($data['dia_semana']) ? intval($data['dia_semana']) : 0;
$hora_inicio = isset($data['hora_inicio']) ? sanitize($data['hora_inicio']) : '';
$hora_fin = isset($data['hora_fin']) ? sanitize($data['hora_fin']) : '';

if ($clase_id <= 0 || $dia_semana < 1 || $dia_semana > 7 || empty($hora_inicio) || empty($hora_fin)) {
    jsonResponse(['success' => false, 'message' => 'Todos los campos son requeridos y deben ser válidos'], 400);
}

try {
    $db = Database::getInstance()->getConnection();

    // Verify clase exists
    $stmt = $db->prepare("SELECT nombre FROM clases WHERE id = ?");
    $stmt->execute([$clase_id]);
    $clase = $stmt->fetch();
    if (!$clase) {
        jsonResponse(['success' => false, 'message' => 'La clase no existe'], 404);
    }

    // Insert horario
    $stmt = $db->prepare("INSERT INTO horarios (clase_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
    $stmt->execute([$clase_id, $dia_semana, $hora_inicio, $hora_fin]);

    $id = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Horario creado exitosamente',
        'data' => [
            'id' => $id,
            'clase_id' => $clase_id,
            'clase_nombre' => $clase['nombre'],
            'dia_semana' => $dia_semana,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin
        ]
    ], 201);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al crear el horario'], 500);
}
