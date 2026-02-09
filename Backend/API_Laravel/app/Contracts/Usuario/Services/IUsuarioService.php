<?php

namespace App\Contracts\Usuario\Services;

use App\DTOs\Usuario\EditarPerfil\InputDto;


interface IUsuarioService
{
    public function update(int $id, InputDTO $dto);
}

