<?php
/**
 * QR Tambo - List Clases API
 * GET: Returns all dance classes
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM clases ORDER BY nombre ASC");
    $clases = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $clases]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al obtener clases'], 500);
}
