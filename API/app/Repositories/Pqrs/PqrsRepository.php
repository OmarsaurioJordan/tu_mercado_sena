<?php

namespace App\Repositories\Pqrs;

use App\Models\Pqrs;
use App\Contracts\Pqrs\IPqrsRepository;
use Illuminate\Support\Facades\Log;


class PqrsRepository implements IPqrsRepository
{
    public function create(array $data): Pqrs
    {
        // Loguear la información de la creación para monitoreo y depuración
        Log::info("Repository: Creando una nueva Pqrs con los siguientes datos: ", [
            "usuario_id" => $data['usuario_id'],
        ]);

        return Pqrs::create($data);
    }

    /**
     * Contar el número de Pqrs creadas por un usuario específico.
     * @param int $usuario_id - El Id del usuario para el cual se desea contar las Pqrs.
     * @return int - El número de Pqrs creadas por el usuario.
     */
    public function countByUsuarioId(int $usuarioId): int
    {
        // Loguear la información de la creación para monitoreo y depuración
        Log::info("Repository: Contando Pqrs para el usuario ID: " . $usuarioId, []);

        return Pqrs::where('usuario_id', $usuarioId)->count();
    }

    /**
     * Cuenta cuántas PQRS del usuario están en estado resuelto.
     * @param int $usuarioId
     * @return int
     */
    public function countResolvedPqrs(int $usuarioId): int
    {
        Log::info("Repository: Contando Pqrs resueltas para el usuario ID: " . $usuarioId, []);

        return Pqrs::where('usuario_id', $usuarioId)
            ->where('estado_id', 11)
            ->count();
    }
}
