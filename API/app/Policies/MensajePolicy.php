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
        $usuarioId = $cuenta->usuario->id;
        $chat = $mensaje->chat;

        // Si esto falla, es porque no cargaste 'chat.producto' en el controlador
        $vendedorId = $chat->producto->vendedor->id;
        $compradorId = $chat->comprador->id;

        // Lógica basada en tu base de datos (tinyint es_comprador)
        $esAutor = $mensaje->es_comprador 
                ? $compradorId == $usuarioId 
                : $vendedorId == $usuarioId;

        return $esAutor;
    }
}
