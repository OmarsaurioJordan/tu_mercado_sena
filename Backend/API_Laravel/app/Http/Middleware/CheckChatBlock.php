<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class CheckChatBlock
{
    public function handle(Request $request, Closure $next): Response
    {
        $chat = $request->route('chat');

        if (!$chat instanceof Chat) {
            $chat = Chat::with(['producto.vendedor', 'comprador'])
                ->findOrFail($chat);
        }

        $usuarioAutenticado = Auth::user()->usuario;

        $comprador = $chat->comprador;
        $vendedor = $chat->producto->vendedor;

        // 1️⃣ Validar que el usuario pertenezca al chat
        if (
            $usuarioAutenticado->id !== $comprador->id &&
            $usuarioAutenticado->id !== $vendedor->id
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para interactuar con este chat',
                'code' => 'CHAT_FORBIDDEN'
            ], 403);
        }

        // 2️⃣ Identificar al otro usuario
        $otroUsuario = $usuarioAutenticado->id === $comprador->id
            ? $vendedor
            : $comprador;

        // 3️⃣ Verificar bloqueo mutuo
        $existeBloqueo =
            $usuarioAutenticado->usuariosQueHeBloqueado()
                ->where('bloqueado_id', $otroUsuario->id)
                ->exists()
            ||
            $otroUsuario->usuariosQueHeBloqueado()
                ->where('bloqueado_id', $usuarioAutenticado->id)
                ->exists();

        if ($existeBloqueo) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes enviar mensajes porque uno de los usuarios ha bloqueado al otro.',
                'code' => 'USER_BLOCKED'
            ], 403);
        }

        return $next($request);
    }
}

