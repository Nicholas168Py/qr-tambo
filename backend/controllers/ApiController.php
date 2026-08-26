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
            $errorCode = $e->getCode();
            $message = 'Error del servidor';
            if ($errorCode === '23000' || $errorCode === 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062)) {
                $message = 'Este registro ya existe (duplicado)';
            }
            error_log('[API] PDOException: ' . $e->getMessage() . ' | Code: ' . $errorCode);
            Http::jsonResponse(['success' => false, 'message' => $message, 'code' => $errorCode], 500);
        } catch (\Throwable $e) {
            error_log('[API] Error no controlado: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            Http::jsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
        }
    }
}
