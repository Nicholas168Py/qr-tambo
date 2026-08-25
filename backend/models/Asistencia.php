<?php
/**
 * QR Tambo - Modelo Asistencia
 * Única responsabilidad: acceso a datos de la tabla `asistencia`.
 * No contiene lógica de negocio.
 */

namespace Models;

final class Asistencia {
    private static function db() {
        return \Database::getInstance()->getConnection();
    }

    public static function filter(array $filters): array {
        $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
        $perPage = isset($filters['per_page']) ? min(100, max(1, (int)$filters['per_page'])) : 10;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['cedula'])) {
            $where .= " AND cedula = ?";
            $params[] = $filters['cedula'];
        }

        if (!empty($filters['clase'])) {
            $where .= " AND clase = ?";
            $params[] = $filters['clase'];
        }

        if (!empty($filters['mes']) && $filters['mes'] >= 1 && $filters['mes'] <= 12) {
            $where .= " AND MONTH(fecha) = ?";
            $params[] = $filters['mes'];
        }

        if (!empty($filters['anio']) && $filters['anio'] > 0) {
            $where .= " AND YEAR(fecha) = ?";
            $params[] = $filters['anio'];
        }

        $sqlCount = "SELECT COUNT(*) as total FROM asistencia $where";
        $stmtCount = self::db()->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetch()['total'];

        $sql = "SELECT id, cedula, nombre, clase, fecha, hora_registro FROM asistencia $where ORDER BY fecha DESC, hora_registro DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ]
        ];
    }

    public static function exists(string $cedula, string $clase, string $fecha): bool {
        $stmt = self::db()->prepare(
            "SELECT id FROM asistencia WHERE cedula = ? AND clase = ? AND fecha = ?"
        );
        $stmt->execute([$cedula, $clase, $fecha]);
        return (bool) $stmt->fetch();
    }

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare(
            "SELECT id, cedula, nombre, clase, fecha, hora_registro FROM asistencia WHERE id = ?"
        );
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function create(string $cedula, string $nombre, string $clase, string $fecha, string $horaRegistro): int {
        $stmt = self::db()->prepare(
            "INSERT INTO asistencia (cedula, nombre, clase, fecha, hora_registro) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$cedula, $nombre, $clase, $fecha, $horaRegistro]);
        return (int) self::db()->lastInsertId();
    }

    public static function monthlySummary(int $mes, int $anio): array {
        $db = self::db();
        $summary = [];

        $stmt = $db->prepare(
            "SELECT COUNT(*) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?"
        );
        $stmt->execute([$mes, $anio]);
        $summary['total_registros'] = $stmt->fetch()['total'];

        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT cedula) as total FROM asistencia WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?"
        );
        $stmt->execute([$mes, $anio]);
        $summary['bailarines_unicos'] = $stmt->fetch()['total'];

        $stmt = $db->prepare(
            "SELECT clase, COUNT(*) as total FROM asistencia
             WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
             GROUP BY clase ORDER BY total DESC"
        );
        $stmt->execute([$mes, $anio]);
        $summary['por_clase'] = $stmt->fetchAll();

        $stmt = $db->prepare(
            "SELECT cedula, nombre, COUNT(*) as total FROM asistencia
             WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
             GROUP BY cedula, nombre ORDER BY total DESC"
        );
        $stmt->execute([$mes, $anio]);
        $summary['por_bailarin'] = $stmt->fetchAll();

        $stmt = $db->prepare(
            "SELECT fecha, COUNT(*) as total FROM asistencia
             WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
             GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([$mes, $anio]);
        $summary['por_dia'] = $stmt->fetchAll();

        return $summary;
    }
}
