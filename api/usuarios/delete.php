<?php
/**
 * QR Tambo - Delete Usuario API
 * POST: id
 * Admin only - cannot delete other admins
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID de usuario inválido'], 400);
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    if ($user['rol'] !== 'bailarin') {
        jsonResponse(['success' => false, 'message' => 'No puedes eliminar administradores'], 403);
    }

    $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'bailarin'");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'No se pudo eliminar el usuario'], 500);
    }

    jsonResponse(['success' => true, 'message' => 'Bailarín eliminado correctamente']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al eliminar usuario'], 500);
}
