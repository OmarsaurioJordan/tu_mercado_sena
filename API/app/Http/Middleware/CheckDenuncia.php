<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Producto;

class CheckDenuncia
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        // si viene producto_id validar existencia
        $productoId = $request->input('producto_id');
        if ($productoId) {
            if (!Producto::where('id', $productoId)->exists()) {
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }
        }

        // evitar denuncias recurrentes: verificar por contexto específico
        $usuarioReportado = $request->input('usuario_id');
        $denuncianteId = Auth::user()->usuario->id;

        // Priorizar chequeo por chat (más específico)
        $chatId = $request->input('chat_id');
        if ($chatId) {
            $exists = \App\Models\Denuncia::where('denunciante_id', $denuncianteId)
                ->where('chat_id', $chatId)
                ->where('estado_id', 1)
                ->exists();
            if ($exists && $request->isMethod('post')) {
                return response()->json(['message' => 'Ya has denunciado este chat.'], 422);
            }
        }

        // Si no es por chat, revisar si es por producto
        $productoId = $request->input('producto_id');
        if ($productoId) {
            $exists = \App\Models\Denuncia::where('denunciante_id', $denuncianteId)
                ->where('producto_id', $productoId)
                ->where('estado_id', 1)
                ->exists();
            if ($exists && $request->isMethod('post')) {
                return response()->json(['message' => 'Ya has denunciado este producto.'], 422);
            }
        }

        // Si sólo se denuncia al usuario (sin producto ni chat), evitar duplicados de ese tipo
        if ($usuarioReportado && empty($productoId) && empty($chatId)) {
            $exists = \App\Models\Denuncia::where('denunciante_id', $denuncianteId)
                ->where('usuario_id', $usuarioReportado)
                ->whereNull('producto_id')
                ->whereNull('chat_id')
                ->where('estado_id', 1)
                ->exists();
            if ($exists && $request->isMethod('post')) {
                return response()->json(['message' => 'Ya has denunciado a este usuario.'], 422);
            }
        }

        return $next($request);
    }
}
