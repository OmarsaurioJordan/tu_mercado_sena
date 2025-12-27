<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IUsuarioService;
use App\Contracts\Usuario\Repositories\IUsuarioReposory;
use App\DTOs\Usuario\EditarPerfil\InputDto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;


class UsuarioService implements IUsuarioService
{
    public function __construct(
        private IUsuarioReposory $usuarioRepository
    ) 
    {}

    public function update(int $id, InputDto $dto)
    {
        $authUserId = auth()->id();
    
        if ($authUserId !== $id) {
            throw new AuthorizationException(
                'No puedes editar el perfil de otro usuario.'
            );
        }
    
        $usuario = $this->usuarioRepository->findById($id);
    
        if (!$usuario) {
            throw new ModelNotFoundException(
                'Usuario no encontrado'
            );
        }
    
        return $this->usuarioRepository->update($id, $dto->toArray());
    }
}
