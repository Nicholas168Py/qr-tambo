<?php
/**
 * QR Tambo - Soporte Date
 * Utilidades de formato de fechas (nombres de día y mes en español).
 */

namespace Support;

final class Date {
    public static function dayName(int $dayNumber): string {
        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        return $days[$dayNumber] ?? 'Desconocido';
    }

    public static function monthName(int $monthNumber): string {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$monthNumber] ?? 'Desconocido';
    }
}
