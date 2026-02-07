<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Mensaje\Services\IMensajeService;
use App\DTOs\Chat\OutputDetailsDto;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Mensaje\StoreMessageRequest;
use App\Models\Mensaje;
use App\DTOs\Mensaje\InputDto;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class MensajeController extends Controller
{
    public function __construct(
        protected IMensajeService $mensajeService
    ){}

    public function store(Chat $chat, StoreMessageRequest $request)
    {
        $dto = InputDto::fromRequest(
            array_merge(
                $request->validated(),
                [
                    'chat_id' => $chat->id,
                    'es_comprador' => $chat->comprador_id === Auth::user()->usuario->id
                ]
            )
        );

        $mensaje = $chat->mensajes()->create($dto->toArray());

        $chat->load(['producto', 'producto.fotos', 'producto.vendedor']);

        $mensajesPaginados = $chat
            ->mensajes()
            ->orderBy('fecha_registro', 'desc')
            ->paginate(20);

        
        return response()->json([
            'status' => 'success',
            'chat_detalle' => OutputDetailsDto::fromModel($chat, false, $mensajesPaginados)->toArray(),
            'nuevo_mensaje' => $mensaje
        ], 201);
    }

    public function destroy(Chat $chat, Mensaje $mensaje)
    {   
        if ($mensaje->chat_id !== $chat->id) {
                abort(403, 'El mensaje no pertenece al chat especificado.'); 
        }

        $mensaje->load('chat.producto');

        // Validar que solo los usuarios del chat y el creador del mensaje puedan eliminarlo
        $this->authorize('delete', $mensaje);

        $mensaje->delete($mensaje->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mensaje eliminado correctamente'
        ], 200);
    }
}
