<?php
/**
 * QR Tambo - Rutas de la API REST
 * Mapa centralizado: método HTTP + patrón -> [Controlador, método].
 * Los segmentos {id} capturan identificadores numéricos.
 */

use Controllers\AsistenciaController;
use Controllers\AuthController;
use Controllers\ClaseController;
use Controllers\HorarioController;
use Controllers\UsuarioController;

return [
    // Auth
    ['POST', '/auth/login', [AuthController::class, 'login']],
    ['POST', '/auth/register', [AuthController::class, 'register']],
    ['POST', '/auth/logout', [AuthController::class, 'logout']],
    ['GET', '/auth/me', [AuthController::class, 'me']],
    ['POST', '/auth/cambiar-password', [UsuarioController::class, 'changePassword']],
    ['POST', '/auth/cambiar-credenciales', [UsuarioController::class, 'changeCredentials']],

    // Clases
    ['GET', '/clases', [ClaseController::class, 'index']],
    ['POST', '/clases', [ClaseController::class, 'store']],
    ['PUT', '/clases/{id}', [ClaseController::class, 'update']],
    ['DELETE', '/clases/{id}', [ClaseController::class, 'destroy']],

    // Horarios
    ['GET', '/horarios', [HorarioController::class, 'index']],
    ['POST', '/horarios', [HorarioController::class, 'store']],
    ['GET', '/horarios/clases-por-dia', [HorarioController::class, 'classesForDay']],
    ['PUT', '/horarios/{id}', [HorarioController::class, 'update']],
    ['DELETE', '/horarios/{id}', [HorarioController::class, 'destroy']],

    // Asistencia
    ['GET', '/asistencia', [AsistenciaController::class, 'index']],
    ['POST', '/asistencia/registrar', [AsistenciaController::class, 'register']],
    ['GET', '/asistencia/reporte-mensual', [AsistenciaController::class, 'monthlyReport']],
    ['GET', '/asistencia/{id}', [AsistenciaController::class, 'show']],

    // Usuarios
    ['GET', '/usuarios', [UsuarioController::class, 'index']],
    ['GET', '/usuarios/{id}', [UsuarioController::class, 'show']],
    ['PUT', '/usuarios/{id}', [UsuarioController::class, 'update']],
    ['DELETE', '/usuarios/{id}', [UsuarioController::class, 'destroy']],
];
