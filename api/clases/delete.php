<?php
/**
 * QR Tambo - Delete Clase API
 * POST: id
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID de clase inválido'], 400);
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM clases WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Clase no encontrada'], 404);
    }

    jsonResponse(['success' => true, 'message' => 'Clase eliminada']);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al eliminar la clase'], 500);
}
