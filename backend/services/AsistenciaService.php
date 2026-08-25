<?php
/**
 * QR Tambo - Servicio Asistencia
 * Lógica de negocio de asistencia: filtrado, registro por QR y reportes.
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Asistencia;
use Models\Clase;
use Models\Horario;
use Support\ApiException;
use Support\Date;

final class AsistenciaService {
    public function listFor(array $user, array $filters): array {
        $cedulaFilter = $user['rol'] === 'bailarin'
            ? $user['cedula']
            : ($filters['cedula'] ?? null);

        return Asistencia::filter([
            'cedula' => $cedulaFilter,
            'clase' => $filters['clase'] ?? null,
            'mes' => $filters['mes'] ?? null,
            'anio' => $filters['anio'] ?? null,
            'page' => $filters['page'] ?? null,
            'per_page' => $filters['per_page'] ?? null
        ]);
    }

    public function register(array $user, string $token): array {
        $decoded = $this->decodeQrToken($token);
        $clase = $decoded['c'];
        $fecha = $decoded['f'];

        $this->assertValidDate($fecha);
        $this->assertValidClassSchedule($decoded, $fecha);

        if (Asistencia::exists($user['cedula'], $clase, $fecha)) {
            throw new ApiException('Ya registraste tu asistencia a esta clase hoy', 409);
        }

        $horaRegistro = date('H:i:s');
        Asistencia::create($user['cedula'], $user['nombre'], $clase, $fecha, $horaRegistro);

        return [
            'nombre' => $user['nombre'],
            'clase' => $clase,
            'fecha' => $fecha,
            'hora_registro' => $horaRegistro
        ];
    }

    public function show(int $id, array $user): array {
        $record = Asistencia::findById($id);

        if (!$record) {
            throw new ApiException('Registro de asistencia no encontrado', 404);
        }

        if ($user['rol'] === 'bailarin' && $record['cedula'] !== $user['cedula']) {
            throw new ApiException('No tienes permisos para ver este registro', 403);
        }

        return $record;
    }

    public function monthlyReport(?int $mes, ?int $anio): array {
        $mes = ($mes !== null && $mes >= 1 && $mes <= 12) ? $mes : (int) date('n');
        $anio = ($anio !== null && $anio >= 2020) ? $anio : (int) date('Y');

        return Asistencia::monthlySummary($mes, $anio) + [
            'mes' => $mes,
            'anio' => $anio,
            'mes_nombre' => Date::monthName($mes)
        ];
    }

    private function decodeQrToken(string $token): array {
        $decodedRaw = base64_decode($token, true);
        $decoded = $decodedRaw ? json_decode($decodedRaw, true) : null;

        $debugInfo = '';
        if ($decodedRaw === false) {
            $debugInfo = ' (base64 inválido: ' . substr($token, 0, 30) . '...)';
        } elseif ($decoded === null) {
            $debugInfo = ' (JSON inválido: ' . substr($decodedRaw, 0, 50) . '...)';
        } elseif (!isset($decoded['c'])) {
            $debugInfo = ' (falta campo "c". claves: ' . implode(',', array_keys($decoded)) . ')';
        } elseif (!isset($decoded['f'])) {
            $debugInfo = ' (falta campo "f". claves: ' . implode(',', array_keys($decoded)) . ')';
        }

        if (!$decoded || !isset($decoded['c']) || !isset($decoded['f'])) {
            throw new ApiException('Código QR inválido' . $debugInfo, 400);
        }

        return $decoded;
    }

    private function assertValidDate(string $fecha): void {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if ($fecha !== $today && $fecha !== $yesterday) {
            throw new ApiException('Este código QR no es válido para la fecha actual.', 400);
        }
    }

    /**
     * Verifica que la clase del QR exista y esté realmente programada
     * para el día de la fecha indicada. Evita registrar asistencia con
     * un QR forjado o con parámetros manipulados (otra clase, otro día
     * o una clase inexistente).
     */
    private function assertValidClassSchedule(array $decoded, string $fecha): void {
        $diaSemana = (int) date('N', strtotime($fecha));
        $clase = trim($decoded['c'] ?? '');
        $hora = isset($decoded['h']) ? $decoded['h'] : null;
        $horarioId = isset($decoded['i']) ? (int) $decoded['i'] : 0;

        if ($horarioId > 0) {
            $horario = Horario::findById($horarioId);

            if (!$horario) {
                throw new ApiException('Este código QR no corresponde a una clase válida', 400);
            }

            if ((int) $horario['dia_semana'] !== $diaSemana) {
                throw new ApiException('Este código QR no corresponde a la fecha indicada', 400);
            }

            if ($hora !== null && $hora !== '' && $horario['hora_inicio'] !== $hora) {
                throw new ApiException('Este código QR no corresponde al horario de la clase', 400);
            }

            return;
        }

        $existing = Clase::findByName($clase);

        if (!$existing) {
            throw new ApiException('Este código QR no corresponde a una clase válida', 400);
        }

        $horarios = Horario::findByClaseAndDay((int) $existing['id'], $diaSemana);

        if (empty($horarios)) {
            throw new ApiException('Este código QR no corresponde a una clase programada para la fecha indicada', 400);
        }

        if ($hora !== null && $hora !== '') {
            foreach ($horarios as $item) {
                if ($item['hora_inicio'] === $hora) {
                    return;
                }
            }
            throw new ApiException('Este código QR no corresponde al horario de la clase', 400);
        }
    }
}
