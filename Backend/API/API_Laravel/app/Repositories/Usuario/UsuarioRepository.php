<?php

namespace App\Repositories\Usuario;

use App\Contracts\Usuario\Repositories\IUsuarioRepository;
use App\Models\Usuario;

class UsuarioRepository implements IUsuarioRepository
{
    /**
     * Buscar usuario en la base de datos por su id
     * @param int $id - Id del usuario
     * @param array $data - Datos del usuario a actualizar
     * @return Usuario $usuario
     */
    public function update(int $id, array $data): User
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update($data);

        return $user;
    }
}
