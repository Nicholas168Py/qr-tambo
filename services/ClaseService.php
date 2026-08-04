<?php
/**
 * QR Tambo - Servicio Clase
 * Lógica de negocio sobre las clases de baile (CRUD).
 * No conoce detalles de HTTP.
 */

namespace Services;

use Models\Clase;
use Support\ApiException;

final class ClaseService {
    public function list(): array {
        return Clase::all();
    }

    public function show(int $id): array {
        $clase = Clase::findById($id);

        if (!$clase) {
            throw new ApiException('Clase no encontrada', 404);
        }

        return $clase;
    }

    public function create(string $nombre, string $descripcion): array {
        return Clase::create($nombre, $descripcion);
    }

    public function update(int $id, string $nombre, string $descripcion): array {
        if (!Clase::findById($id)) {
            throw new ApiException('Clase no encontrada', 404);
        }

        Clase::update($id, $nombre, $descripcion);

        return [
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ];
    }

    public function destroy(int $id): void {
        if (!Clase::delete($id)) {
            throw new ApiException('Clase no encontrada', 404);
        }
    }
}
