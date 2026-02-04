<?php

namespace App\Services\Mensaje;

use App\Contracts\Mensaje\Repository\IMensajeRepository;
use App\Contracts\Mensaje\Services\IMensajeService;
use App\DTOs\Mensaje\InputDto;
use App\Models\Mensaje;
use Illuminate\Support\Facades\DB;


class MensajeService implements IMensajeService
{
    public function __construct(private IMensajeRepository $mensajeRepository)
    {}

    public function crearMensaje(InputDto $dto): Mensaje
    {
        return DB::transaction(function () use ($dto) {
            $mensaje = $this->mensajeRepository->create($dto->toArray());

            if (!$mensaje) {
                throw new \Exception("No se pudo crear el mensaje, Intente nuevamente.");
            }
            return $mensaje;
        });
    }

    public function delete(int $id_mensaje): bool
    {
        return DB::transaction(function () use ($id_mensaje) {
           $mensajeBorrado = $this->mensajeRepository->delete($id_mensaje);

            if (!$mensajeBorrado) {
                throw new \Exception("No se pudo eliminar el mensaje, Intente nuevamente.");
            }

            return $mensajeBorrado;
        });
    }
}
