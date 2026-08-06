<?php
/**
 * QR Tambo - Controlador Clase
 * Recibe las peticiones de clases, delega en el servicio y
 * construye las respuestas HTTP. No contiene lógica de negocio.
 */

namespace Controllers;

use Services\ClaseService;
use Support\ApiException;
use Support\Auth;
use Support\Http;

final class ClaseController extends ApiController {
    private ClaseService $service;

    public function __construct() {
        $this->service = new ClaseService();
    }

    public function index(): void {
        Auth::requireLogin();

        $this->handle(fn () => $this->service->list(), 200, 'Clases obtenidas');
    }

    public function store(): void {
        Auth::requireAdmin();

        $data = Http::getRequestBody();
        $nombre = Http::sanitize($data['nombre'] ?? '');
        $descripcion = Http::sanitize($data['descripcion'] ?? '');

        if ($nombre === '') {
            throw new ApiException('El nombre de la clase es requerido', 400);
        }

        $this->handle(fn () => $this->service->create($nombre, $descripcion), 201, 'Clase creada exitosamente');
    }

    public function update(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de clase inválido', 400);
        }

        $data = Http::getRequestBody();
        $nombre = Http::sanitize($data['nombre'] ?? '');
        $descripcion = Http::sanitize($data['descripcion'] ?? '');

        if ($nombre === '') {
            throw new ApiException('El nombre de la clase es requerido', 400);
        }

        $this->handle(fn () => $this->service->update($id, $nombre, $descripcion), 200, 'Clase actualizada exitosamente');
    }

    public function destroy(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de clase inválido', 400);
        }

        $this->handle(function () use ($id) {
            $this->service->destroy($id);
            return null;
        }, 200, 'Clase eliminada');
    }
}
