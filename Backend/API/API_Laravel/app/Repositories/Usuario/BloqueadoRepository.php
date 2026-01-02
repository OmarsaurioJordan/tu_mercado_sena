<?php

namespace App\Repositories\Usuario;

use App\Contracts\Usuario\Repositories\IBloqueadoRepository;
use App\Models\Bloqueado;
use Illuminate\Database\Eloquent\Collection;


class BloqueadoRepository implements IBloqueadoRepository
{
    public function __construct()
    {}

    public function bloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado
    {
        return Bloqueado::firstOrCreate([
            'bloqueador_id' => $bloqueadorId,
            'bloqueado_id' => $bloqueadoId
        ]);
    }

    public function desbloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado
    {
        return Bloqueado::where('bloqueador_id', $bloqueadorId)
            ->where('bloqueado_id', $bloqueadoId)
            ->delete();
    }

    public function estaBloqueado(int $bloqueadorId, int $bloqueadoId): bool
    {
        return Bloqueado::where('bloqueador_id', $bloqueadorId)
            ->where('bloqueado_id', $bloqueadoId)
            ->exists();
    }

    public function obtenerBloqueadosPorUsuario(int $bloqueadorId): Collection
    {
        return Bloqueado::with('bloqueado')
            ->where('bloqueador_id', $bloqueadorId)
            ->get();
    }
}
