<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$data = getRequestBody();
$currentPassword = isset($data['current_password']) ? $data['current_password'] : '';
$newPassword = isset($data['new_password']) ? $data['new_password'] : '';
$confirmPassword = isset($data['confirm_password']) ? $data['confirm_password'] : '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    jsonResponse(['success' => false, 'message' => 'Completa todos los campos'], 400);
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden'], 400);
}

if (strlen($newPassword) < 6) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

try {
    $db = Database::getInstance()->getConnection();
    $user = getCurrentUser();

    // Verify current password
    $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    // Support both old plain-text and bcrypt hashed passwords
    $storedHash = $row['password_hash'];
    $isValid = false;
    if (password_needs_rehash($storedHash, PASSWORD_DEFAULT) && $currentPassword === $storedHash) {
        // Plain-text match → hash it now
        $isValid = true;
    } elseif (password_verify($currentPassword, $storedHash)) {
        $isValid = true;
    }

    if (!$isValid) {
        jsonResponse(['success' => false, 'message' => 'La contraseña actual no es correcta'], 400);
    }

    // Update password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $user['id']]);

    jsonResponse(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al actualizar la contraseña'], 500);
}
