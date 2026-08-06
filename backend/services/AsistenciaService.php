<?php
/**
 * QR Tambo - Servicio Asistencia
 * Lógica de negocio de asistencia: filtrado, registro por QR y reportes.
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Asistencia;
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
            'anio' => $filters['anio'] ?? null
        ]);
    }

    public function register(array $user, string $token): array {
        $decoded = $this->decodeQrToken($token);
        $clase = $decoded['c'];
        $fecha = $decoded['f'];

        $this->assertValidDate($fecha);

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
            throw new ApiException('Este código QR ha expirado. Solo es válido para el día de la clase.', 400);
        }
    }
}
