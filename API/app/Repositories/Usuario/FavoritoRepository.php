<?php

namespace App\Repositories\Usuario;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;


class FavoritoRepository
{
    public function index(int $usuarioId): Collection
    {
        $usuario = Usuario::findOrFail($usuarioId);
        return $usuario->favoritos()->get();
    }

    public function store(int $votanteId, int $votadoId): Usuario
    {
        $usuarioVotante = Usuario::findOrFail($votanteId);
        
        // Crear registro en la tabla favoritos sin eliminar los existentes
        $usuarioVotante->favoritos()->syncWithoutDetaching([$votadoId]);

        // Retornar el usuario votado con la información del pivot para confirmar que se agregó correctamente
        return $usuarioVotante->favoritos()
            ->where('votado_id', $votadoId) // Filtramos por el que acabamos de votar
            ->withPivot('id', 'votante_id', 'votado_id')  // Asegúrate de pedir los campos del pivot
            ->firstOrFail();
    }

    public function destroy(int $votanteId, int $votadoId): void
    {
        $usuario = Usuario::findOrFail($votanteId);

        $usuario->favoritos()->detach($votadoId);
    }

}
