<?php

namespace App\Repositories\Mensaje;

use App\Contracts\Mensaje\Repository\IMensajeRepository;
use App\Models\Mensaje;

class MensajeRepository implements IMensajeRepository
{
    public function create(array $datos): Mensaje
    {
        return Mensaje::create($datos);
    }

    public function delete(int $id): bool
    {
        $mensaje = Mensaje::find($id);

        if (!$mensaje) {
            return false;
        }

        return $mensaje->delete();
    }
}
