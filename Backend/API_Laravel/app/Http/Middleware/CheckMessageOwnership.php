<?php

namespace App\Http\Middleware;

use App\Models\Mensaje;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckMessageOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el mensajes de la ruta ruta: (/api/mensajes/mensaje)
        $mensaje_id = $request->route('mensaje');
        $mensaje = Mensaje::with('chat.producto')->findOrFail($mensaje_id);
        
        // Obtener usuario autenticado
        $usuario_autenticado = Auth::user()->usuario;
        $chat = $mensaje->chat;

        // Determinar el rol del usuario actual en este chat
        $es_comprador_del_chat = ($usuario_autenticado->id === $chat->comprador_id);
        $es_vendedor_del_chat = ($usuario_autenticado->id === $chat->producto->vendedor_id);


        // Validar autoría
        $puede_borrar = ($mensaje->es_comprador && $es_comprador_del_chat) ||
                        (!$mensaje->es_comprador && $es_vendedor_del_chat);

        if (!$puede_borrar) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar este mensaje'
            ], 403);
        }
        
        return $next($request);
    }
}
