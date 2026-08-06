<?php
/**
 * QR Tambo - Bootstrap
 * Configuración de entorno, sesiones, autoloader de capas y helpers globales.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

// Log de errores a archivo (logs/php_errors.log) para diagnosticar 500 en producción
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
if (is_writable($logDir)) {
    ini_set('log_errors', '1');
    ini_set('error_log', $logDir . '/php_errors.log');
}

// Cargar config para APP_TIMEZONE y credenciales
require_once __DIR__ . '/config.php';

// Zona horaria de la academia (corrige desfases de hora en asistencia/QR)
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'America/Guayaquil');

// Ensure sessions work on InfinityFree (writable path)
$sessDir = __DIR__ . '/../sessions';
if (!is_dir($sessDir)) {
    @mkdir($sessDir, 0777, true);
}
if (is_writable($sessDir)) {
    session_save_path($sessDir);
}

// Set JSON header early for API calls
$isApi = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
if ($isApi) {
    header('Content-Type: application/json; charset=utf-8');
}

// Session persistence: keep session until explicit logout
$tenYears = 10 * 365 * 24 * 60 * 60;
ini_set('session.cookie_lifetime', $tenYears);
ini_set('session.gc_maxlifetime', $tenYears);
ini_set('session.cache_expire', $tenYears / 60);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => $tenYears,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/database.php';

// Autoloader PSR-4 simplificado para las capas de la aplicación
spl_autoload_register(function ($class) {
    $prefixes = [
        'Models\\' => __DIR__ . '/../models/',
        'Services\\' => __DIR__ . '/../services/',
        'Controllers\\' => __DIR__ . '/../controllers/',
        'Support\\' => __DIR__ . '/../support/'
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            break;
        }
    }
});

// Helpers globales (delegan a la capa Support) para compatibilidad con páginas y scripts existentes.

function isLoggedIn() { return \Support\Auth::isLoggedIn(); }

function requireLogin() { return \Support\Auth::requireLogin(); }

function requireAdmin() { return \Support\Auth::requireAdmin(); }

function getCurrentUser() { return \Support\Auth::currentUser(); }

function jsonResponse($data, $code = 200) { \Support\Http::jsonResponse($data, $code); }

function getRequestBody() { return \Support\Http::getRequestBody(); }

function sanitize($input) { return \Support\Http::sanitize($input); }

function getDayName($dayNumber) { return \Support\Date::dayName($dayNumber); }

function getMonthName($monthNumber) { return \Support\Date::monthName($monthNumber); }
