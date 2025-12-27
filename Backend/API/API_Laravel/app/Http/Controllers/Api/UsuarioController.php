<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Usuario\EditarPerfilRequest;
use App\DTOs\Usuario\EditarPerfil\InputDto;
use App\Contracts\Usuario\Services\IUsuarioService;


class UsuarioController
{
    public function __construct(
        private IUsuarioService $usuarioService
    ) 
    {}

    public function update(int $id, EditarPerfilRequest $request)
    {
        $dto = InputDto::fromRequest($request->validated());

        $perfil = $this->usuarioService->update($id, $dto);

        return response()->json($perfil);
    }
}
