<?php

namespace App\Contracts\Mensaje\Services;
use App\DTOs\Mensaje\InputDto;
use App\Models\Mensaje;
interface IMensajeService
{
    public function crearMensaje(InputDto $dto): Mensaje;

    public function delete(int $id_mensaje): bool;
}
