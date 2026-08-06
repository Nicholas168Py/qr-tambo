<?php
/**
 * QR Tambo - Modelo Clase
 * Única responsabilidad: acceso a datos de la tabla `clases`.
 * No contiene lógica de negocio.
 */

namespace Models;

final class Clase {
    private static function db() {
        return \Database::getInstance()->getConnection();
    }

    public static function all(): array {
        return self::db()->query("SELECT * FROM clases ORDER BY nombre ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM clases WHERE id = ?");
        $stmt->execute([$id]);
        $clase = $stmt->fetch();
        return $clase ?: null;
    }

    public static function create(string $nombre, string $descripcion): array {
        $stmt = self::db()->prepare("INSERT INTO clases (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre, $descripcion]);
        return [
            'id' => (int) self::db()->lastInsertId(),
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ];
    }

    public static function update(int $id, string $nombre, string $descripcion): bool {
        $stmt = self::db()->prepare("UPDATE clases SET nombre = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([$nombre, $descripcion, $id]);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM clases WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
