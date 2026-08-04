<?php
/**
 * QR Tambo - Servicio Usuario
 * Lógica de negocio sobre usuarios (listado, consulta, actualización, borrado y contraseñas).
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Usuario;
use Support\ApiException;

final class UsuarioService {
    public function listBailarines(): array {
        return [
            'data' => Usuario::allBailarines(),
            'total' => Usuario::countBailarines()
        ];
    }

    public function show(int $id): array {
        $user = Usuario::findByIdPublic($id);

        if (!$user) {
            throw new ApiException('Usuario no encontrado', 404);
        }

        return $user;
    }

    public function update(int $id, string $nombre): array {
        if ($nombre === '') {
            throw new ApiException('El nombre es requerido', 422);
        }

        $user = Usuario::findById($id);

        if (!$user) {
            throw new ApiException('Usuario no encontrado', 404);
        }

        Usuario::updateName($id, $nombre);

        return [
            'id' => $id,
            'cedula' => $user['cedula'],
            'nombre' => $nombre,
            'rol' => $user['rol']
        ];
    }

    public function destroy(int $id): void {
        $user = Usuario::findById($id);

        if (!$user) {
            throw new ApiException('Usuario no encontrado', 404);
        }

        if ($user['rol'] !== 'bailarin') {
            throw new ApiException('No puedes eliminar administradores', 403);
        }

        if (!Usuario::deleteBailarin($id)) {
            throw new ApiException('No se pudo eliminar el usuario', 500);
        }
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): void {
        if ($newPassword !== $confirmPassword) {
            throw new ApiException('Las contraseñas nuevas no coinciden', 400);
        }

        if (strlen($newPassword) < 6) {
            throw new ApiException('La contraseña debe tener al menos 6 caracteres', 400);
        }

        $user = Usuario::findById($userId);

        if (!$user) {
            throw new ApiException('Usuario no encontrado', 404);
        }

        $this->verifyCurrentPassword($user['password_hash'], $currentPassword);

        Usuario::updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    private function verifyCurrentPassword(string $storedHash, string $currentPassword): void {
        $isPlainTextMatch = password_needs_rehash($storedHash, PASSWORD_DEFAULT) && $currentPassword === $storedHash;
        $isHashMatch = password_verify($currentPassword, $storedHash);

        if (!$isPlainTextMatch && !$isHashMatch) {
            throw new ApiException('La contraseña actual no es correcta', 400);
        }
    }
}
