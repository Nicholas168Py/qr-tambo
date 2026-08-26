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
        // Regenerate session ID to prevent fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['cedula'] = $user['cedula'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
    }

    /**
     * Extiende la vida de la cookie de sesión a 30 días.
     * Se usa cuando el usuario activa "Recordarme".
     */
    public static function extendCookieLifetime(): void {
        // La sesión ya tiene lifetime de 30 días por defecto.
        // Este método es para cuando se necesita extender aún más.
        if (session_status() !== PHP_SESSION_ACTIVE) return;

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

        setcookie(session_name(), session_id(), [
            'expires' => time() + (365 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        ini_set('session.cookie_lifetime', 365 * 24 * 60 * 60);
    }

    public static function logout(): void {
        $_SESSION = [];
        session_unset();
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $isHttps,
                'httponly' => $params['httponly'],
                'samesite' => 'Lax'
            ]);
        }
    }
}
