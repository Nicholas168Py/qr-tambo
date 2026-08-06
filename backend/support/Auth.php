<?php
/**
 * QR Tambo - Soporte Auth
 * Gestión de sesión y guardas de autenticación/autorización.
 */

namespace Support;

final class Auth {
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            Http::jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión para acceder'], 401);
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            Http::jsonResponse(['success' => false, 'message' => 'No tienes permisos de administrador'], 403);
        }
    }

    public static function currentUser(): ?array {
        if (!self::isLoggedIn()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'cedula' => $_SESSION['cedula'],
            'nombre' => $_SESSION['nombre'],
            'rol' => $_SESSION['rol']
        ];
    }

    public static function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['cedula'] = $user['cedula'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
    }

    public static function logout(): void {
        $_SESSION = [];
        session_unset();
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax'
            ]);
        }
    }
}
