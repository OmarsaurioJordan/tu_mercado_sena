<?php

namespace App\Repositories\Usuario;

use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use App\Models\Bloqueado;
use Illuminate\Database\Eloquent\Collection;


class BloqueadoRepository implements IBloqueadoRepository
{
    public function __construct()
    {}

    /**
     * Función para bloquear un usuario que retorna el modelo creado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Bloqueado
     */
    public function bloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado
    {
        return Bloqueado::firstOrCreate([
            'bloqueador_id' => $bloqueadorId,
            'bloqueado_id' => $bloqueadoId
        ]);
    }

    /**
     * Función para desbloquear un usuario que retorna el modelo eliminado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Bloqueado|null El modelo eliminado o null si no se encontró
     */
    public function desbloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado
    {
        $registro = Bloqueado::where('bloqueador_id', $bloqueadorId)
            ->where('bloqueado_id', $bloqueadoId)
            ->first();
        
        if ($registro) {
            $registro->delete();
        }

        return $registro;
    }

    /**
     * Función de apoyo para verificar si un usuario ha bloqueado a otro
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return bool
     */
    public function estaBloqueado(int $bloqueadorId, int $bloqueadoId): bool
    {
        return Bloqueado::where('bloqueador_id', $bloqueadorId)
            ->where('bloqueado_id', $bloqueadoId)
            ->exists();
    }

    /**
     * Función para obtener la lista de usuarios bloqueados por un usuario que retorna una colección de modelos Bloqueado
     * @param int $bloqueadorId
     * @return Collection<int, Bloqueado>
     */
    public function obtenerBloqueadosPorUsuario(int $bloqueadorId): Collection
    {
        # Función with para cargar la relación 'bloqueado' y no tener el problema de N+1 queries
        return Bloqueado::with('bloqueado')
            ->where('bloqueador_id', $bloqueadorId)
            ->get();
    }
}
