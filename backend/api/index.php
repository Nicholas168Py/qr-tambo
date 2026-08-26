<?php
/**
 * QR Tambo - Front controller de la API REST
 * Todas las peticiones a /api/* pasan por este archivo.
 */

require __DIR__ . '/../config/init.php';

use Support\Router;

// Anti-cache headers para API responses
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// CORS headers for mobile browser compatibility
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost',
    'http://localhost:5173',
    'http://127.0.0.1',
    'http://127.0.0.1:5173',
];
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

$routes = require __DIR__ . '/../routes/api.php';
foreach ($routes as [$method, $pattern, $handler]) {
    $router->add($method, $pattern, $handler);
}

// Ruta relativa a la carpeta /api
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$path = ($base !== '' && strpos($uri, $base) === 0)
    ? substr($uri, strlen($base))
    : $uri;

$router->dispatch($path);
