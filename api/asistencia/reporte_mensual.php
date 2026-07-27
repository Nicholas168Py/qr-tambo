<?php
/**
 * QR Tambo - Monthly Attendance Report API
 * GET: mes, anio (defaults to current month/year)
 * Admin only
 */
require_once __DIR__ . '/../../config/init.php';
requireAdmin();

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('n'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

if ($mes < 1 || $mes > 12) $mes = intval(date('n'));
if ($anio < 2020) $anio = intval(date('Y'));

try {
    $db = Database::getInstance()->getConnection();

    // Total records
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
    $stmt->execute([$mes, $anio]);
    $totalRegistros = $stmt->fetch()['total'];

    // Unique bailarines
    $stmt = $db->prepare("SELECT COUNT(DISTINCT cedula) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
    $stmt->execute([$mes, $anio]);
    $bailarinesUnicos = $stmt->fetch()['total'];

    // By class
    $stmt = $db->prepare(
        "SELECT clase, COUNT(*) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? GROUP BY clase ORDER BY total DESC"
    );
    $stmt->execute([$mes, $anio]);
    $porClase = $stmt->fetchAll();

    // By bailarin
    $stmt = $db->prepare(
        "SELECT cedula, nombre, COUNT(*) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? GROUP BY cedula, nombre ORDER BY total DESC"
    );
    $stmt->execute([$mes, $anio]);
    $porBailarin = $stmt->fetchAll();

    // By day
    $stmt = $db->prepare(
        "SELECT fecha, COUNT(*) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? GROUP BY fecha ORDER BY fecha ASC"
    );
    $stmt->execute([$mes, $anio]);
    $porDia = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => [
            'total_registros' => $totalRegistros,
            'bailarines_unicos' => $bailarinesUnicos,
            'por_clase' => $porClase,
            'por_bailarin' => $porBailarin,
            'por_dia' => $porDia,
            'mes' => $mes,
            'anio' => $anio,
            'mes_nombre' => getMonthName($mes)
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Error al generar reporte'], 500);
}
