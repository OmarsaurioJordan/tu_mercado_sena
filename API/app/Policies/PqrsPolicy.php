<?php

namespace App\Policies;

use App\Models\Cuenta;
use App\Models\Pqrs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\Response;

class PqrsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Cuenta $cuenta): bool
    {
        return $cuenta->usuario->id === Auth::user()->usuario->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Cuenta $cuenta): bool
    {
        return $cuenta->usuario->id === Auth::user()->usuario->id;
    }
}
