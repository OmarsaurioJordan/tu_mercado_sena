<?php

namespace App\Contracts\Usuario\Repositories;

use App\Models\Bloqueado;
use Illuminate\Database\Eloquent\Collection;


interface IBloqueadoRepository
{
    public function bloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado;

    public function desbloquearUsuario(int $bloqueadorId, int $bloqueadoId): Bloqueado;

    public function estaBloqueado(int $bloqueadorId, int $bloqueadoId): bool;

    public function obtenerBloqueadosPorUsuario(int $bloqueadorId): Collection;
}
