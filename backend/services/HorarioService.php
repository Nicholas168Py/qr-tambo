<?php
/**
 * QR Tambo - Servicio Horario
 * Lógica de negocio sobre horarios semanales (CRUD) y consulta de clases por día.
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Clase;
use Models\Horario;
use Support\ApiException;
use Support\Date;

final class HorarioService {
    public function list(?int $dia): array {
        return Horario::all($dia);
    }

    public function show(int $id): array {
        $horario = Horario::findById($id);

        if (!$horario) {
            throw new ApiException('Horario no encontrado', 404);
        }

        return $horario;
    }

    public function create(int $claseId, int $diaSemana, string $horaInicio, string $horaFin): array {
        $clase = Clase::findById($claseId);

        if (!$clase) {
            throw new ApiException('La clase no existe', 404);
        }

        $id = Horario::create($claseId, $diaSemana, $horaInicio, $horaFin);

        return $this->buildPayload($id, $claseId, $clase['nombre'], $diaSemana, $horaInicio, $horaFin);
    }

    public function update(int $id, int $claseId, int $diaSemana, string $horaInicio, string $horaFin): array {
        $clase = Clase::findById($claseId);

        if (!$clase) {
            throw new ApiException('La clase no existe', 404);
        }

        if (!Horario::findById($id)) {
            throw new ApiException('Horario no encontrado', 404);
        }

        Horario::update($id, $claseId, $diaSemana, $horaInicio, $horaFin);

        return $this->buildPayload($id, $claseId, $clase['nombre'], $diaSemana, $horaInicio, $horaFin);
    }

    public function destroy(int $id): void {
        if (!Horario::delete($id)) {
            throw new ApiException('Horario no encontrado', 404);
        }
    }

    public function classesForDay(?int $dia): array {
        $day = $this->normalizeDay($dia);
        $clases = Horario::all($day);

        return [
            'data' => $clases,
            'dia' => $day,
            'dia_nombre' => Date::dayName($day),
            'fecha' => date('Y-m-d'),
            'total' => count($clases)
        ];
    }

    private function buildPayload(int $id, int $claseId, string $claseNombre, int $diaSemana, string $horaInicio, string $horaFin): array {
        return [
            'id' => $id,
            'clase_id' => $claseId,
            'clase_nombre' => $claseNombre,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin
        ];
    }

    private function normalizeDay(?int $dia): int {
        return ($dia !== null && $dia >= 1 && $dia <= 7) ? $dia : (int) date('N');
    }
}
