<?php
/**
 * QR Tambo - Front controller de la API REST
 * Todas las peticiones a /api/* pasan por este archivo.
 */

require __DIR__ . '/../config/init.php';

use Support\Router;

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
