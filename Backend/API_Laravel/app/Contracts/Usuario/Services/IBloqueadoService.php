<?php

namespace App\Contracts\Usuario\Services;

use App\DTOs\Usuario\Bloqueados\InputDto;


interface IBloqueadoService
{
    public function ejecutarBloqueo(InputDto $dto): array;

    public function ejecutarDesbloqueo(InputDto $dto): array;

    public function solicitarBloqueadosPorUsuario(int $bloqueadorId): array;
}
