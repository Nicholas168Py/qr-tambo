<?php
/**
 * QR Tambo - Logout API
 */
require_once __DIR__ . '/../../config/init.php';

$_SESSION = [];
session_unset();
session_destroy();

// Delete session cookie
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

jsonResponse(['success' => true, 'message' => 'Sesión cerrada']);
