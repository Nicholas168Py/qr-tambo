<?php
/**
 * QR Tambo - Controlador Auth
 * Recibe las peticiones de autenticación, delega en el servicio y
 * construye las respuestas HTTP. No contiene lógica de negocio.
 */

namespace Controllers;

use Services\AuthService;
use Support\ApiException;
use Support\Auth;
use Support\Http;

final class AuthController extends ApiController {
    private AuthService $service;

    public function __construct() {
        $this->service = new AuthService();
    }

    public function login(): void {
        $data = Http::getRequestBody();
        $cedula = trim($data['cedula'] ?? '');
        $password = $data['password'] ?? '';
        $remember = !empty($data['remember']);

        if ($cedula === '' || $password === '') {
            throw new ApiException('Cédula y contraseña son requeridos', 400);
        }

        error_log('[AUTH] Login attempt for cedula=' . $cedula . ' remember=' . ($remember ? '1' : '0'));

        $this->handle(function () use ($cedula, $password, $remember) {
            $user = $this->service->login($cedula, $password);
            Auth::login($user);

            if ($remember) {
                Auth::extendCookieLifetime();
            }

            return [
                'nombre' => $user['nombre'],
                'cedula' => $user['cedula'],
                'rol' => $user['rol']
            ];
        }, 200, 'Inicio de sesión exitoso');
    }

    public function register(): void {
        $data = Http::getRequestBody();
        $nombre = trim($data['nombre'] ?? '');
        $cedula = trim($data['cedula'] ?? '');
        $password = $data['password'] ?? '';

        if ($nombre === '' || $cedula === '' || $password === '') {
            throw new ApiException('Todos los campos son requeridos', 400);
        }

        error_log('[AUTH] Register attempt for cedula=' . $cedula . ', nombre=' . $nombre);

        $this->handle(function () use ($nombre, $cedula, $password) {
            $this->service->register($nombre, $cedula, $password);
            return null;
        }, 201, 'Registro exitoso. Ya puedes iniciar sesión.');
    }

    public function logout(): void {
        Auth::logout();
        $this->handleRaw(fn () => ['success' => true, 'message' => 'Sesión cerrada']);
    }

    public function me(): void {
        if (!Auth::isLoggedIn()) {
            Http::jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión para acceder'], 401);
            return;
        }
        $this->handleRaw(fn () => [
            'success' => true,
            'data' => Auth::currentUser()
        ]);
    }
}
