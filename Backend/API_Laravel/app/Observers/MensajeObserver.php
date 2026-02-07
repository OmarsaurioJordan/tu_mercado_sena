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

        if ($mensaje->es_comprador === true) {
            $chat->update([
                'visto_comprador' => true,
                'visto_vendedor' => false,
            ]);
        } else {
            $chat->update([
                'visto_vendedor' => true,
                'visto_comprador' => false,
            ]);
        }
    }
}
