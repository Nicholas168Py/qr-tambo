<?php
/**
 * QR Tambo - Controlador Asistencia
 * Recibe las peticiones de asistencia, delega en el servicio y
 * construye las respuestas HTTP. No contiene lógica de negocio.
 */

namespace Controllers;

use Services\AsistenciaService;
use Support\ApiException;
use Support\Auth;
use Support\Http;

final class AsistenciaController extends ApiController {
    private AsistenciaService $service;

    public function __construct() {
        $this->service = new AsistenciaService();
    }

    public function index(): void {
        Auth::requireLogin();

        $user = Auth::currentUser();
        $filters = [
            'cedula' => isset($_GET['cedula']) ? trim($_GET['cedula']) : '',
            'clase' => isset($_GET['clase']) ? trim($_GET['clase']) : '',
            'mes' => isset($_GET['mes']) ? (int) $_GET['mes'] : 0,
            'anio' => isset($_GET['anio']) ? (int) $_GET['anio'] : 0,
            'page' => isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1,
            'per_page' => isset($_GET['per_page']) ? min(100, max(1, (int) $_GET['per_page'])) : 10
        ];

        $this->handleRaw(function () use ($user, $filters) {
            return ['success' => true, 'message' => 'Asistencia obtenida'] + $this->service->listFor($user, $filters);
        }, 200);
    }

    public function show(int $id): void {
        Auth::requireLogin();

        if ($id <= 0) {
            throw new ApiException('ID de registro inválido', 400);
        }

        $user = Auth::currentUser();

        $this->handle(fn () => $this->service->show($id, $user), 200, 'Registro obtenido');
    }

    public function register(): void {
        Auth::requireLogin();

        $data = Http::getRequestBody();
        $token = $data['token'] ?? '';

        if ($token === '') {
            throw new ApiException('Token QR requerido', 400);
        }

        $user = Auth::currentUser();

        $this->handle(fn () => $this->service->register($user, $token), 200, '¡Asistencia registrada exitosamente!');
    }

    public function monthlyReport(): void {
        Auth::requireAdmin();

        $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : 0;
        $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : 0;

        $this->handle(fn () => $this->service->monthlyReport($mes, $anio), 200, 'Reporte generado');
    }
}
