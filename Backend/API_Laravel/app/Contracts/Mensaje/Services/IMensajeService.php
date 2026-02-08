<?php

namespace App\Contracts\Mensaje\Services;
use App\DTOs\Mensaje\InputDto;
use App\Models\Mensaje;
use App\Models\Chat;

interface IMensajeService
{
    public function crearMensaje(InputDto $dto, Chat $chat): Array;

    public function delete(Chat $chat, Mensaje $mensaje): bool;
}
