<?php
/**
 * QR Tambo - List Horarios API
 * GET: Optional param ?dia=N (1-7)
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$dia = isset($_GET['dia']) ? intval($_GET['dia']) : 0;

try {
    $db = Database::getInstance()->getConnection();

    $sql = "SELECT h.id, h.clase_id, h.dia_semana, h.hora_inicio, h.hora_fin, c.nombre as clase_nombre, c.descripcion as clase_descripcion 
            FROM horarios h 
            JOIN clases c ON h.clase_id = c.id";
    $params = [];

    if ($dia >= 1 && $dia <= 7) {
        $sql .= " WHERE h.dia_semana = ?";
        $params[] = $dia;
    }

    $sql .= " ORDER BY h.dia_semana, h.hora_inicio";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $horarios = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $horarios]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al obtener horarios'], 500);
}
