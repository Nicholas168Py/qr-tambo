<?php
/**
 * QR Tambo - Create Clase API
 * POST: nombre, descripcion
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$nombre = isset($data['nombre']) ? sanitize($data['nombre']) : '';
$descripcion = isset($data['descripcion']) ? sanitize($data['descripcion']) : '';

if (empty($nombre)) {
    jsonResponse(['success' => false, 'message' => 'El nombre de la clase es requerido'], 400);
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO clases (nombre, descripcion) VALUES (?, ?)");
    $stmt->execute([$nombre, $descripcion]);

    $id = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Clase creada exitosamente',
        'data' => ['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion]
    ], 201);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al crear la clase'], 500);
}
