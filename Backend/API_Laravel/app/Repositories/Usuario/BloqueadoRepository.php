<?php

namespace App\Repositories\Usuario;

use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;


class BloqueadoRepository implements IBloqueadoRepository
{
    public function __construct()
    {}

    /**
     * Función para bloquear un usuario que retorna el modelo creado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Usuario
     */
    public function bloquearUsuario(int $bloqueadorId, int $bloqueadoId): Usuario
    {
        $usuario = Usuario::find($bloqueadorId);
        
        $usuario->usuariosQueHeBloqueado()->syncWithoutDetaching([$bloqueadoId]);

        return $usuario;
    }

    /**
     * Función para desbloquear un usuario que retorna el modelo eliminado
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return Usuario|null El modelo eliminado o null si no se encontró
     */
    public function desbloquearUsuario(int $bloqueadorId, int $bloqueadoId): Usuario
    {
        
        $usuario = Usuario::find($bloqueadorId);

        $usuario->usuariosQueHeBloqueado()->detach($bloqueadoId);

        return $usuario;
        // $registro = Bloqueado::where('bloqueador_id', $bloqueadorId)
        //     ->where('bloqueado_id', $bloqueadoId)
        //     ->first();
        
        // if ($registro) {
        //     $registro->delete();
        // }

        // return $registro;
    }

    /**
     * Función de apoyo para verificar si un usuario ha bloqueado a otro
     * @param int $bloqueadorId
     * @param int $bloqueadoId
     * @return bool
     */
    public function estaBloqueado(int $bloqueadorId, int $bloqueadoId): bool
    {   
        $usuario = Usuario::find($bloqueadorId);
        return $usuario->usuariosQueHeBloqueado()->where('bloqueado_id', $bloqueadoId)->exists();
        // return Bloqueado::where('bloqueador_id', $bloqueadorId)
        //     ->where('bloqueado_id', $bloqueadoId)
        //     ->exists();
    }

    /**
     * Función para obtener la lista de usuarios bloqueados por un usuario que retorna una colección de modelos Bloqueado
     * @param int $bloqueadorId
     * @return Collection<int, Bloqueado>
     */
    public function obtenerBloqueadosPorUsuario(int $bloqueadorId): Collection
    {
        $usuario = Usuario::findOrFail($bloqueadorId);

        return $usuario->usuariosQueHeBloqueado()->get();

    }
}
