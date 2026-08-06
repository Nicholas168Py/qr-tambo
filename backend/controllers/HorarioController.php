<?php
/**
 * QR Tambo - Controlador Horario
 * Recibe las peticiones de horarios, delega en el servicio y
 * construye las respuestas HTTP. No contiene lógica de negocio.
 */

namespace Controllers;

use Services\HorarioService;
use Support\ApiException;
use Support\Auth;
use Support\Http;

final class HorarioController extends ApiController {
    private HorarioService $service;

    public function __construct() {
        $this->service = new HorarioService();
    }

    public function index(): void {
        Auth::requireLogin();

        $dia = isset($_GET['dia']) ? (int) $_GET['dia'] : 0;

        $this->handle(fn () => $this->service->list($dia), 200, 'Horarios obtenidos');
    }

    public function store(): void {
        Auth::requireAdmin();

        $data = Http::getRequestBody();
        $claseId = isset($data['clase_id']) ? (int) $data['clase_id'] : 0;
        $diaSemana = isset($data['dia_semana']) ? (int) $data['dia_semana'] : 0;
        $horaInicio = Http::sanitize($data['hora_inicio'] ?? '');
        $horaFin = Http::sanitize($data['hora_fin'] ?? '');

        if ($claseId <= 0 || $diaSemana < 1 || $diaSemana > 7 || $horaInicio === '' || $horaFin === '') {
            throw new ApiException('Todos los campos son requeridos y deben ser válidos', 400);
        }

        $this->handle(fn () => $this->service->create($claseId, $diaSemana, $horaInicio, $horaFin), 201, 'Horario creado exitosamente');
    }

    public function update(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de horario inválido', 400);
        }

        $data = Http::getRequestBody();
        $claseId = isset($data['clase_id']) ? (int) $data['clase_id'] : 0;
        $diaSemana = isset($data['dia_semana']) ? (int) $data['dia_semana'] : 0;
        $horaInicio = Http::sanitize($data['hora_inicio'] ?? '');
        $horaFin = Http::sanitize($data['hora_fin'] ?? '');

        if ($claseId <= 0 || $diaSemana < 1 || $diaSemana > 7 || $horaInicio === '' || $horaFin === '') {
            throw new ApiException('Todos los campos son requeridos y deben ser válidos', 400);
        }

        $this->handle(fn () => $this->service->update($id, $claseId, $diaSemana, $horaInicio, $horaFin), 200, 'Horario actualizado exitosamente');
    }

    public function destroy(int $id): void {
        Auth::requireAdmin();

        if ($id <= 0) {
            throw new ApiException('ID de horario inválido', 400);
        }

        $this->handle(function () use ($id) {
            $this->service->destroy($id);
            return null;
        }, 200, 'Horario eliminado');
    }

    public function classesForDay(): void {
        Auth::requireLogin();

        $dia = isset($_GET['dia']) ? (int) $_GET['dia'] : 0;

        $this->handleRaw(function () use ($dia) {
            return ['success' => true] + $this->service->classesForDay($dia);
        });
    }
}
