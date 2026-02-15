<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Contracts\Chat\Services\IChatService;
use App\DTOs\Chat\InputDto;
use App\DTOs\Chat\UpdateInputDto;
use App\Http\Requests\Chat\CompraventaRequest;
use App\Models\Chat;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\IniciarCompraventa;
use App\Http\Requests\Chat\IniciarCompraventaRequest;
use App\Http\Requests\Chat\ModifyChatRequest;
use App\Http\Requests\Chat\TerminarCompraventa;
use App\Http\Requests\Chat\TerminarCompraventaRequest;
use Illuminate\Support\Facades\Request;

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

    public function iniciarCompraVenta(IniciarCompraventaRequest $request, Chat $chat)
    {
        $this->authorize('update', $chat);

        $dto = UpdateInputDto::fromRequest($request->validated());

        $chatActualizado = $this->chatService->iniciarCompraventa($chat, $dto);

        return response()->json($chatActualizado, 200);
    }

        // Si es comprador
        // $resultado = $this->chatService->terminarCompraventa($chat,$request->validated()['confirmacion']);

        // return response()->json($resultado, 200);
    public function terminarCompraVenta(TerminarCompraventaRequest $request, Chat $chat)
    {
        $this->authorize('update', $chat);

        $resultado = $this->chatService->terminarCompraventa($chat, $request->validated());

        return response()->json($resultado, 200);
    }

    public function iniciarDevoluciones(Chat $chat)
    {
        // Policy que solo permite al vendedor y al comprador interactuar con este método
        $this->authorize('update', $chat);

        // Obtener el id del usuario que hace la petición
        $usuarioId = Auth::user()->usuario->id;

        // Variable que guarda el array de confirmación que viene del servicio
        $resultado = $this->chatService->iniciarDevolucion($chat, $usuarioId);

        // Retornar json
        return response()->json($resultado, 200);
    }

    public function terminarDevolucion(Chat $chat)
    {
        // Policy que solo permite al vendedor y al comprador interactuar con este método
        $this->authorize('update', $chat);

        // Obtener el id del usuario que hace la petición
        $usuarioId = Auth::user()->usuario->id;

        // Variable que guarda el array de confirmación que viene del servicio
        $resultado = $this->chatService->terminarDevolucion($chat, $usuarioId);

        // Retornar json
        return response()->json($resultado, 200);
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