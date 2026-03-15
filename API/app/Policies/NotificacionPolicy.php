<?php

namespace App\Policies;

use App\Models\Cuenta;
use App\Models\Notificacion;
use Illuminate\Auth\Access\Response;

class NotificacionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(Cuenta $cuenta, Notificacion $notificacion): bool
    {
        return $cuenta->usuario->id === $notificacion->usuario_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Cuenta $cuenta, Notificacion $notificacion): bool
    {
        return $cuenta->usuario->id === $notificacion->usuario_id;
    }
}
