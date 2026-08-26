<?php
/**
 * QR Tambo - Servicio Auth
 * Lógica de negocio de autenticación y registro de usuarios.
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Usuario;
use Support\ApiException;

final class AuthService {
    public function login(string $cedula, string $password): array {
        $user = Usuario::findByCedula($cedula);

        if (!$user) {
            error_log('[AUTH] Login failed: user not found for cedula=' . $cedula);
            throw new ApiException('Cédula o contraseña incorrectos', 401);
        }

        $this->verifyPassword($user, $password);

        error_log('[AUTH] Login successful for cedula=' . $cedula . ', rol=' . $user['rol']);
        return $user;
    }

    public function register(string $nombre, string $cedula, string $password): void {
        if (strlen($password) < 6) {
            throw new ApiException('La contraseña debe tener al menos 6 caracteres', 400);
        }

        if (Usuario::existsByCedula($cedula)) {
            throw new ApiException('Esta cédula ya está registrada', 409);
        }

        try {
            Usuario::create($cedula, $nombre, password_hash($password, PASSWORD_DEFAULT));
            error_log('[AUTH] Register successful for cedula=' . $cedula);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062)) {
                throw new ApiException('Esta cédula ya está registrada', 409);
            }
            throw $e;
        }
    }

    private function verifyPassword(array $user, string $password): void {
        $storedHash = $user['password_hash'];

        if (password_verify($password, $storedHash)) {
            return;
        }

        // Fallback: contraseñas en texto plano durante la migración a hashes.
        if ($password === $storedHash) {
            error_log('[AUTH] Migrating plain text password to hash for user=' . $user['id']);
            Usuario::updatePassword($user['id'], password_hash($password, PASSWORD_DEFAULT));
            return;
        }

        error_log('[AUTH] Password verification failed for user=' . $user['id'] . ', hash starts with: ' . substr($storedHash, 0, 10));
        throw new ApiException('Cédula o contraseña incorrectos', 401);
    }
}
