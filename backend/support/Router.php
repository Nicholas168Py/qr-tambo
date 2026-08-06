<?php
/**
 * QR Tambo - Soporte Router
 * Enrutador REST basado en métodos HTTP y patrones de ruta.
 * No contiene lógica de negocio.
 */

namespace Support;

final class Router {
    private const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void {
        $this->routes[strtoupper($method)][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $path): void {
        $path = '/' . trim($path, '/');
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes[$requestMethod] ?? [] as $route) {
            $params = $this->match($route['pattern'], $path);

            if ($params !== null) {
                $this->call($route['handler'], $params);
                return;
            }
        }

        $allowed = [];
        foreach (self::METHODS as $method) {
            foreach ($this->routes[$method] ?? [] as $route) {
                if ($this->match($route['pattern'], $path) !== null) {
                    $allowed[] = $method;
                }
            }
        }

        if ($allowed) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            Http::jsonResponse(['success' => false, 'message' => 'Método no permitido para esta ruta'], 405);
        }

        Http::jsonResponse(['success' => false, 'message' => 'Ruta no encontrada'], 404);
    }

    private function match(string $pattern, string $path): ?array {
        $pattern = '/' . trim($pattern, '/');
        $regex = preg_replace('/\{[a-zA-Z_]+\}/', '(\d+)', $pattern);

        if (preg_match('#^' . $regex . '$#', $path, $matches)) {
            array_shift($matches);
            return array_map('intval', $matches);
        }

        return null;
    }

    private function call(array $handler, array $params): void {
        [$class, $action] = $handler;

        try {
            $controller = new $class();
            $controller->$action(...$params);
        } catch (ApiException $e) {
            Http::jsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getCode());
        } catch (\PDOException $e) {
            Http::jsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
        } catch (\Throwable $e) {
            error_log('[API] Error no controlado: ' . $e->getMessage());
            Http::jsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
        }
    }
}
