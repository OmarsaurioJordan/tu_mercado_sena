<?php

namespace App\Services\Mensaje;

use App\Contracts\Mensaje\Repository\IMensajeRepository;
use App\Contracts\Mensaje\Services\IMensajeService;
use App\DTOs\Mensaje\InputDto;
use App\Models\Chat;
use App\Models\Mensaje;
use Illuminate\Support\Facades\DB;


class MensajeService implements IMensajeService
{
    public function __construct(private IMensajeRepository $mensajeRepository)
    {}

    public function crearMensaje(InputDto $dto, Chat $chat): array
    {
        return DB::transaction(function () use ($dto, $chat) {
            
            $mensaje = $this->mensajeRepository->create($dto->toArray());

            if (!$mensaje) {
                throw new \Exception("No se pudo crear el mensaje, Intente nuevamente.");
            }

            if ($chat->estado_id === 4 || $chat->estado_id === 5) {
                $chat->update(['estado_id' => 1]);
            }

            $chat->load(['producto', 'producto.fotos', 'producto.vendedor']);

            $mensajesPaginados = $chat
                ->mensajes()
                ->orderBy('fecha_registro', 'desc')
                ->paginate(20);

            if (!$mensajesPaginados) {
                throw new \Exception("No se pudieron cargar los mensajes, Intente nuevamente.");
            }

            if ($mensaje->es_comprador) {
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

            return [
                'success' => true,
                'mensaje' => $mensaje,
                'chat_detalle' => $chat,
                'mensajes_paginados' => $mensajesPaginados
            ];
        });
    }

    public function delete(Mensaje $mensaje): bool
    {
        $chat = $mensaje->chat;

        // Validar que el mensaje pertenezca al chat especificado
        if ($mensaje->chat_id !== $chat->id) {
            throw new \Exception("El mensaje no pertenece al chat especificado.");
        }

        return DB::transaction(function () use ($mensaje) {
           $mensajeBorrado = $this->mensajeRepository->delete($mensaje->id);

            if (!$mensajeBorrado) {
                throw new \Exception("No se pudo eliminar el mensaje, Intente nuevamente.");
            }

            return $mensajeBorrado;
        });
    }
}
