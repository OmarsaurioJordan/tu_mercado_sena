<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Contracts\Chat\Services\IChatService;
use App\DTOs\Chat\InputDto;
use App\DTOs\Chat\UpdateInputDto;
use App\Models\Chat;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\ModifyChatRequest;

class ChatController extends Controller
{
    public function __construct(
        protected IChatService $chatService
        )
    {}

    public function index()
    {
        $this->authorize('viewAny', Chat::class);
        // Mostrar la lista de los chats
        $usuario_id = Auth::user()->usuario->id; 

        return response()->json(
            $this->chatService->obtenerChatsUsuario($usuario_id),
            200
        );
    }

    public function show(Chat $chat)
    {   
        $chat->load('producto');

        // Validar que solo el comprador o el vendedor puedan ver los detalles del chat
        $this->authorize('view', $chat);

        // Obtener el ID del usuario autenticado
        $usuario_id = Auth::user()->usuario->id;

        return response()->json([
            'status' => 'success',
            'data' => $this->chatService->mostrarChat($chat->id, $usuario_id)
        ], 200);
    }

    public function store(Producto $producto, CreateChatRequest $request)
    {

        $usuario_id = Auth::user()->usuario->id;
        $producto_id = $producto->id;
        $estado_id = 1; // Asignar el estado "activo" por defecto

        $dto = InputDto::fromRequest([
            'comprador_id' => $usuario_id,
            'producto_id' => $producto_id,
            'estado_id' => $estado_id
        ]);

        $chat = $this->chatService->iniciarChat($dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat iniciado correctamente',
            'data' => $chat
        ], 201);
    }

    public function update(ModifyChatRequest $request, Chat $chat)
    {   
        // Validar que solo el comprador pueda modificar el chat
        $this->authorize('update', $chat);

        // Crear el DTO a partir de los datos validados de la solicitud
        $dto = UpdateInputDto::fromRequest($request->validated());

        // Actualizar el chat utilizando el servicio
        $chat_actualizado = $this->chatService->actualizarChatComprador($chat->id, $dto);

        // Retornar la respuesta JSON con el chat actualizado
        return response()->json([
            'status' => 'success',
            'message' => 'Chat actualizado correctamente',
            'data' => $chat_actualizado
        ], 200);
    }

    public function destroy(Chat $chat)
    {
        $this->authorize('delete', $chat);

        $usuario_id = Auth::user()->usuario->id;

        $this->chatService->eliminarChat($chat->id, $usuario_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat eliminado correctamente'
        ], 200);
    }
}