<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Mensaje\Services\IMensajeService;
use App\DTOs\Chat\OutputDetailsDto;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Mensaje\StoreMessageRequest;
use App\Models\Mensaje;
use App\DTOs\Mensaje\InputDto;
use App\Models\Chat;

class MensajeController extends Controller
{
    public function __construct(
        protected IMensajeService $mensajeService
    ){}

    public function store(Chat $chat,StoreMessageRequest $request)
    {
        // Crear el dto que guardara la información proveniente del front-end
        $dto = InputDto::fromRequest($request->validated());

        $mensaje = $chat->mensajes()->create($dto->toArray());

        $chat = $chat->with(['producto', 'producto.fotos', 'producto.vendedor'])->first();

        return response()->json([
            'status' => 'success',
            'chat_detalle' => OutputDetailsDto::fromModel($chat, false, null)->toArray(),
            'nuevo_mensaje' => $mensaje
        ], 201);
    }

    public function destroy(Mensaje $mensaje)
    {
        // Validar que solo los usuarios del chat y el creador del mensaje puedan eliminarlo
        $this->authorize('delete', $mensaje);

        $this->mensajeService->delete($mensaje->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mensaje eliminado correctamente'
        ], 200);
    }
}
