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
    @chmod($sessDir, 0777);
}

// Configuración de sesión ROBUSTA para hosting compartido
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessDir);
// Lifetime razonable (30 días) - 10 años causa problemas en GC y locking
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60);
ini_set('session.cache_expire', 30 * 24 * 60);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);      // Evita fixation
ini_set('session.use_trans_sid', 0);        // No URLs con session ID
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);         // 1% probability

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
// Detect if we're on localhost or IP address for mobile compatibility
$isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'], true);
$cookieDomain = $isLocalhost ? '' : ($_SERVER['SERVER_NAME'] ?? '');

session_set_cookie_params([
    'lifetime' => 30 * 24 * 60 * 60,
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => $isHttps ? 'None' : 'Lax'  // None for HTTPS (required for cross-site), Lax for HTTP
]);

// Start session con manejo de errores y recovery
if (session_status() === PHP_SESSION_NONE) {
    $started = @session_start();
    if (!$started) {
        // Intento de recovery: limpiar sesión corrupta y reintentar
        error_log('[SESSION] session_start() falló. save_path=' . session_save_path() . ' writable=' . (is_writable(session_save_path()) ? 'yes' : 'no'));
        
        // Cerrar cualquier sesión residual
        if (session_status() !== PHP_SESSION_NONE) {
            @session_write_close();
        }
        // Limpiar cookie del cliente si existe
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            @setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite']
            ]);
        }
        // Reintentar una vez
        @session_start();
    }
}

// Regenerate session ID on privilege change (security)
if (!empty($_SESSION['__regenerate'])) {
    session_regenerate_id(true);
    unset($_SESSION['__regenerate']);
}

// Set JSON header early for API calls
$isApi = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
if ($isApi) {
    header('Content-Type: application/json; charset=utf-8');
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
