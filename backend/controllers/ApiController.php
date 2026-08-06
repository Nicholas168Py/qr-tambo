<?php
/**
 * QR Tambo - Controlador base de la API
 * Centraliza la construcción de respuestas HTTP y el manejo de errores
 * (errores de negocio y errores de base de datos).
 */

namespace Controllers;

use Support\ApiException;
use Support\Http;

abstract class ApiController {
    protected function handle(callable $buildData, int $successCode = 200, string $message = 'Operación exitosa'): void {
        $this->run(function () use ($buildData, $message) {
            return [
                'success' => true,
                'message' => $message,
                'data' => $buildData()
            ];
        }, $successCode);
    }

    protected function handleRaw(callable $buildBody, int $successCode = 200): void {
        $this->run($buildBody, $successCode);
    }

    private function run(callable $buildResponse, int $successCode): void {
        try {
            Http::jsonResponse($buildResponse(), $successCode);
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
