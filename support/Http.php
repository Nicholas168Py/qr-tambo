<?php
/**
 * QR Tambo - Soporte Http
 * Utilidades del protocolo HTTP (entrada/salida de peticiones).
 * No contiene lógica de negocio.
 */

namespace Support;

final class Http {
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function getRequestBody(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            return $data ?: [];
        }

        return $_POST;
    }

    public static function jsonResponse($data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function noContent(): void {
        http_response_code(204);
        exit;
    }
}
