<?php

namespace App\Observers;

use App\Models\Mensaje;

class MensajeObserver
{
    /**
     * Handle the Mensaje "created" event.
     */
    public function created(Mensaje $mensaje): void
    {
        $chat = $mensaje->chat;

        if ($mensaje->es_comprador) {
            // Si escribio el comprador: él ya lo vio, el vendedor no
            $chat->update([
                'visto_comprador' => true,
                'visto_vendedor' => false
            ]);

        } else {
            // Si escribió el vendedor: él ya lo vio, el comprador no
            $chat->update([
                'visto_vendedor' => true,
                'visto_comprador' => false
            ]);
        }
    }
}
