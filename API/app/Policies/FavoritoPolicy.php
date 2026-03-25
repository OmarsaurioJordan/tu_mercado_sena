<?php

namespace App\Policies;

use App\Models\Cuenta;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class FavoritoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Cuenta $cuenta): bool
    {
        return $cuenta->usuario_id === Auth::user()->usuario->id;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(Cuenta $cuenta): bool
    {
        return $cuenta->usuario_id === Auth::user()->usuario->id;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Cuenta $cuenta, Usuario $usuario): bool
    {
        return $usuario->favoritos()
                   ->where('votado_id', $cuenta->usuario->id)
                   ->exists();
    }
}
