<?php
/**
 * QR Tambo - Modelo Usuario
 * Única responsabilidad: acceso a datos de la tabla `usuarios`.
 * No contiene lógica de negocio.
 */

namespace Models;

final class Usuario {
    private static function db() {
        return \Database::getInstance()->getConnection();
    }

    public static function findByCedula(string $cedula): ?array {
        $stmt = self::db()->prepare(
            "SELECT id, cedula, nombre, password_hash, rol FROM usuarios WHERE cedula = ?"
        );
        $stmt->execute([$cedula]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare(
            "SELECT id, cedula, nombre, password_hash, rol FROM usuarios WHERE id = ?"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByIdPublic(int $id): ?array {
        $stmt = self::db()->prepare(
            "SELECT id, cedula, nombre, rol, created_at FROM usuarios WHERE id = ?"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateName(int $id, string $nombre): bool {
        $stmt = self::db()->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
        return $stmt->execute([$nombre, $id]);
    }

    public static function updateCedula(int $id, string $cedula): bool {
        $stmt = self::db()->prepare("UPDATE usuarios SET cedula = ? WHERE id = ?");
        return $stmt->execute([$cedula, $id]);
    }

    public static function createAdmin(string $cedula, string $nombre, string $passwordHash): int {
        $stmt = self::db()->prepare(
            "INSERT IGNORE INTO usuarios (cedula, nombre, password_hash, rol) VALUES (?, ?, ?, 'admin')"
        );
        $stmt->execute([$cedula, $nombre, $passwordHash]);
        return (int) self::db()->lastInsertId();
    }

    public static function existsByCedula(string $cedula): bool {
        $stmt = self::db()->prepare("SELECT id FROM usuarios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        return (bool) $stmt->fetch();
    }

    public static function allBailarines(): array {
        return self::db()->query(
            "SELECT id, cedula, nombre, rol, created_at
             FROM usuarios
             WHERE rol = 'bailarin'
             ORDER BY nombre ASC"
        )->fetchAll();
    }

    public static function countBailarines(): int {
        $stmt = self::db()->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'bailarin'");
        return (int) $stmt->fetch()['total'];
    }

    public static function create(string $cedula, string $nombre, string $passwordHash): int {
        $stmt = self::db()->prepare(
            "INSERT INTO usuarios (cedula, nombre, password_hash, rol) VALUES (?, ?, ?, 'bailarin')"
        );
        $stmt->execute([$cedula, $nombre, $passwordHash]);
        return (int) self::db()->lastInsertId();
    }

    public static function updatePassword(int $id, string $passwordHash): bool {
        $stmt = self::db()->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }

    public static function deleteBailarin(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'bailarin'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
