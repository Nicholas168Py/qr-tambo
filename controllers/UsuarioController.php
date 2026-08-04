<?php
/**
 * QR Tambo - Controlador Usuario
 * Recibe las peticiones de usuarios, delega en el servicio y
 * construye las respuestas HTTP. No contiene lógica de negocio.
 */

namespace Controllers;

use Services\UsuarioService;
use Support\ApiException;
use Support\Auth;
use Support\Http;

final class UsuarioController extends ApiController {
    private UsuarioService $service;

    public function __construct() {
        $this->service = new UsuarioService();
    }

    public function index(): void {
        Auth::requireAdmin();

        $this->handleRaw(fn () => ['success' => true] + $this->service->listBailarines());
    }

    public function show(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de usuario inválido', 400);
        }

        $this->handle(fn () => $this->service->show($id), 200, 'Usuario obtenido');
    }

    public function update(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de usuario inválido', 400);
        }

        $data = Http::getRequestBody();
        $nombre = Http::sanitize($data['nombre'] ?? '');

        $this->handle(fn () => $this->service->update($id, $nombre), 200, 'Usuario actualizado correctamente');
    }

    public function destroy(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de usuario inválido', 400);
        }

        $this->handle(function () use ($id) {
            $this->service->destroy($id);
            return null;
        }, 200, 'Bailarín eliminado correctamente');
    }

    public function changePassword(): void {
        Auth::requireLogin();

        $data = Http::getRequestBody();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            throw new ApiException('Completa todos los campos', 400);
        }

        $this->handle(function () use ($currentPassword, $newPassword, $confirmPassword) {
            $this->service->changePassword(Auth::currentUser()['id'], $currentPassword, $newPassword, $confirmPassword);
            return null;
        }, 200, 'Contraseña actualizada correctamente');
    }
}
