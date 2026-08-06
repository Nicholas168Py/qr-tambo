<?php
/**
 * QR Tambo - Modelo Horario
 * Única responsabilidad: acceso a datos de la tabla `horarios`.
 * No contiene lógica de negocio.
 */

namespace Models;

final class Horario {
    private static function db() {
        return \Database::getInstance()->getConnection();
    }

    private const SELECT_SQL =
        "SELECT h.id, h.clase_id, h.dia_semana, h.hora_inicio, h.hora_fin,
                c.nombre as clase_nombre, c.descripcion as clase_descripcion
         FROM horarios h
         JOIN clases c ON h.clase_id = c.id";

    public static function all(?int $dia = null): array {
        $sql = self::SELECT_SQL;
        $params = [];

        if ($dia !== null && $dia >= 1 && $dia <= 7) {
            $sql .= " WHERE h.dia_semana = ?";
            $params[] = $dia;
        }

        $sql .= " ORDER BY h.dia_semana, h.hora_inicio";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $claseId, int $diaSemana, string $horaInicio, string $horaFin): int {
        $stmt = self::db()->prepare(
            "INSERT INTO horarios (clase_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$claseId, $diaSemana, $horaInicio, $horaFin]);
        return (int) self::db()->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare(self::SELECT_SQL . " WHERE h.id = ?");
        $stmt->execute([$id]);
        $horario = $stmt->fetch();
        return $horario ?: null;
    }

    public static function update(int $id, int $claseId, int $diaSemana, string $horaInicio, string $horaFin): bool {
        $stmt = self::db()->prepare(
            "UPDATE horarios SET clase_id = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ? WHERE id = ?"
        );
        return $stmt->execute([$claseId, $diaSemana, $horaInicio, $horaFin, $id]);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM horarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
