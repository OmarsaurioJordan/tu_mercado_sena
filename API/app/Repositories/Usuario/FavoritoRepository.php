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
        $usuarioVotante = Usuario::find($votanteId);
            
        // Realiza la acción de favorito
       $usuarioVotante->favoritos()->syncWithoutDetaching([$votadoId]);

        return Usuario::findOrFail($votadoId);
    }

    public function destroy(int $votanteId, int $votadoId): void
    {
        $usuario = Usuario::find($votanteId);

        $usuario->favoritos()->detach($votadoId);
    }

}
