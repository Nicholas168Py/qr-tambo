<?php
/**
 * QR Tambo - Get Classes for a Day
 * GET: ?dia=N (1-7, defaults to current day)
 * Returns ordered list of classes for QR generation
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

// Default to current day (PHP date('N'): 1=Monday ... 7=Sunday)
$dia = isset($_GET['dia']) ? intval($_GET['dia']) : intval(date('N'));

if ($dia < 1 || $dia > 7) {
    $dia = intval(date('N'));
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare(
        "SELECT h.id, h.clase_id, h.dia_semana, h.hora_inicio, h.hora_fin, 
                c.nombre as clase_nombre, c.descripcion as clase_descripcion 
         FROM horarios h 
         JOIN clases c ON h.clase_id = c.id 
         WHERE h.dia_semana = ? 
         ORDER BY h.hora_inicio ASC"
    );
    $stmt->execute([$dia]);
    $clases = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $clases,
        'dia' => $dia,
        'dia_nombre' => getDayName($dia),
        'fecha' => date('Y-m-d'),
        'total' => count($clases)
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al obtener clases del día'], 500);
}
