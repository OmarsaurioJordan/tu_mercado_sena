<?php

namespace App\Contracts\Usuario\Services;

use App\DTOs\Usuario\Bloqueados\InputDto;


interface IBloqueadoService
{
    /**
     * Realiza el bloqueo de un usuario y retorna un array con el resultado.
     * @param InputDto $dto
     * @return array
     */
    public function ejecutarBloqueo(InputDto $dto): array;

    /**
     * Realiza el desbloqueo de un usuario y retorna un array con el resultado.
     * @param InputDto $dto
     * @return array
     */
    public function ejecutarDesbloqueo(InputDto $dto): array;

    /**
     * Solicita la lista de usuarios bloqueados por un usuario específico.
     * @param int $bloqueadorId
     * @return array
     */
    public function solicitarBloqueadosPorUsuario(int $bloqueadorId): array;
}
