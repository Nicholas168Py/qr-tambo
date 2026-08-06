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
            throw new ApiException('Cédula o contraseña incorrectos', 401);
        }

        $this->verifyPassword($user, $password);

        return $user;
    }

    public function register(string $nombre, string $cedula, string $password): void {
        if (strlen($password) < 4) {
            throw new ApiException('La contraseña debe tener al menos 4 caracteres', 400);
        }

        if (Usuario::existsByCedula($cedula)) {
            throw new ApiException('Esta cédula ya está registrada', 409);
        }

        Usuario::create($cedula, $nombre, password_hash($password, PASSWORD_DEFAULT));
    }

    private function verifyPassword(array $user, string $password): void {
        $storedHash = $user['password_hash'];

        if (password_verify($password, $storedHash)) {
            return;
        }

        // Fallback: contraseñas en texto plano durante la migración a hashes.
        if ($password === $storedHash) {
            Usuario::updatePassword($user['id'], password_hash($password, PASSWORD_DEFAULT));
            return;
        }

        throw new ApiException('Cédula o contraseña incorrectos', 401);
    }
}
