<?php

namespace App\Contracts\Usuario\Services;

use App\DTOs\Usuario\Bloqueados\OutputDto;
use Illuminate\Support\Collection;


interface IBloqueadoService
{
    public function ejecutarBloqueo(int $bloqueadorId, int $bloqueadoId): OutputDto;

    public function ejecutarDesbloqueo(int $bloqueadorId, int $bloqueadoId): OutputDto;

    public function solicitarBloqueadosPorUsuario(int $bloqueadorId): Collection;
}
