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
        $dto = InputDto::fromRequest($request->validated());

        $resultado = $this->mensajeService->crearMensaje($dto, $chat);

        return response()->json([
            'status' => 'success',
            'chat_detalle' => OutputDetailsDto::fromModel(
                $resultado['chat_detalle'], 
                false, 
                $resultado['mensajes_paginados']
            )->toArray(),
            'nuevo_mensaje' => $resultado['mensaje']
        ], 201);
    }

    public function destroy(Chat $chat, Mensaje $mensaje)
    {   
        $this->authorize('delete', $mensaje);

        $this->mensajeService->delete($chat, $mensaje);

        return response()->json([
            'status' => 'success',
            'message' => 'Mensaje eliminado correctamente'
        ], 200);
    }
}
