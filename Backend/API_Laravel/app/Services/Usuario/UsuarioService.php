<?php

namespace App\Services\Usuario;

use App\Contracts\Usuario\Services\IUsuarioService;
use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\DTOs\Usuario\EditarPerfil\InputDto;
use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;


class UsuarioService implements IUsuarioService
{
    public function __construct(
        private IUsuarioRepository $usuarioRepository
    ) 
    {}

    public function update(int $id, InputDto $dto)
    {
        $authUserId = Auth::id();
    
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

        $usuario_actualizado = $this->usuarioRepository->update($id, $dto->toArray());
    
        if (!$usuario_actualizado) {
            throw new BusinessException('No se pudo actualizar el perfil del usuario.');
        }

        return $usuario_actualizado;
    }
}
