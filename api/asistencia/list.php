<?php
/**
 * QR Tambo - List Attendance API
 * GET: Optional filters - mes, anio, cedula, clase
 * Bailarines only see their own records
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$user = getCurrentUser();
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
$cedula = isset($_GET['cedula']) ? sanitize($_GET['cedula']) : '';
$clase = isset($_GET['clase']) ? sanitize($_GET['clase']) : '';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "SELECT id, cedula, nombre, clase, fecha, hora_registro FROM asistencia WHERE 1=1";
    $params = [];

    // Bailarines can only see their own records
    if ($user['rol'] === 'bailarin') {
        $sql .= " AND cedula = ?";
        $params[] = $user['cedula'];
    } else {
        // Admin filters
        if (!empty($cedula)) {
            $sql .= " AND cedula = ?";
            $params[] = $cedula;
        }
    }

    if (!empty($clase)) {
        $sql .= " AND clase = ?";
        $params[] = $clase;
    }

    if ($mes >= 1 && $mes <= 12) {
        $sql .= " AND MONTH(fecha) = ?";
        $params[] = $mes;
    }

    if ($anio > 0) {
        $sql .= " AND YEAR(fecha) = ?";
        $params[] = $anio;
    }

    $sql .= " ORDER BY fecha DESC, hora_registro DESC LIMIT 500";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => $records]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al obtener asistencia'], 500);
}
