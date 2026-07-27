<?php
/**
 * QR Tambo - List Usuarios API
 * GET: Lists all bailarines (admin only)
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query(
        "SELECT id, cedula, nombre, rol, created_at 
         FROM usuarios 
         WHERE rol = 'bailarin' 
         ORDER BY nombre ASC"
    );
    $usuarios = $stmt->fetchAll();

    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'bailarin'");
    $total = $stmt->fetch()['total'];

    jsonResponse([
        'success' => true,
        'data' => $usuarios,
        'total' => intval($total)
    ]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al obtener usuarios'], 500);
}
