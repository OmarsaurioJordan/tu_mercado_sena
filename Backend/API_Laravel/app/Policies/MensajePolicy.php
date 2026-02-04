<?php

namespace App\Policies;

use App\Models\Cuenta;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Auth;

class MensajePolicy
{
    /**
     * Determinar quien puede borrar un mensaje.
     */
    public function delete(Cuenta $cuenta, Mensaje $mensaje): bool
    {
        $usuario = $cuenta->usuario;
        $chat = $mensaje->chat;

        $esDelChat = $chat->comprador_id === $usuario->id || $chat->producto->usuario_id === $usuario->id;

        $esCreador = $chat->es_comprador
            ? $chat->comprador_id === $usuario->id
            : $chat->producto->usuario_id === $usuario->id;
            
        return $esDelChat && $esCreador;
    }

}
