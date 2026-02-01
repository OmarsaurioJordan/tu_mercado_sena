<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class CheckChatBlock
{
    /**
     * Verificar si existe un bloqueo entre los participantes del chat
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener la ruta desde el parámetro de la ruta
        $chat = $request->route('chat');

        // Si el chat no es un objeto(es un ID), lo buscamos
        if (!$chat instanceof Chat) {
            $chat = Chat::with(['producto.vendedor', 'comprador'])->findOrFail($chat);
        }

        // Id de la cuenta que es la misma del usuario
        $usuarioAutenticado = Auth::user()->usuario;

        // Identificar al otro usuario en el chat
        $vendedor = $chat->producto->vendedor;
        $comprador = $chat->comprador;

        $otroUsuario = ($usuarioAutenticado->id === $comprador->id) ? $vendedor : $comprador;

        // Verificar el bloqueo mutuo usando la relacion belongsToMany
        $existeBloqueo = $usuarioAutenticado->usuariosQueHeBloqueado()->where('bloqueado_id', $otroUsuario->id)->exists()
                      || $otroUsuario->usuariosQueheBloqueado()->where('bloqueado_id', $usuarioAutenticado->id);

        if ($existeBloqueo) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes realizar esta operación debido a las configuraciones de privacidad de los usuarios involucrados.',
                'code' => 'USER_RESTRICTION'
            ], 403);
        }
        
        return $next($request);
    }
}
