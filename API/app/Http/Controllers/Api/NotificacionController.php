<?php

namespace App\Http\Controllers\Api;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::user()->usuario->id;

        $notificaciones = Notificacion::where('usuario_id', $usuarioId)
            ->orderByDesc('fecha_registro')
            ->get();

        return response()->json([
            'success' => true,
            'message' => $notificaciones->isNotEmpty() ? 'Notificaciones obtenidas correctamente' : 'No hay notificaciones',
            'data' => $notificaciones
        ]);
    }

    public function show(Notificacion $notificacion)
    {
        $this->authorize('view', $notificacion);

        if (!$notificacion->visto) {
            $notificacion->visto = true;
            $notificacion->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificación obtenida correctamente',
            'data' => $notificacion
        ]);
    }

    public function notificacionesNoVistas()
    {
        $usuarioId = Auth::user()->usuario->id;

        $notificacionesNoVistas = Notificacion::where('usuario_id', $usuarioId)
            ->where('visto', false)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Número de notificaciones no vistas obtenidas correctamente',
            'data' => $notificacionesNoVistas
        ]);
    }

    public function destroy(Notificacion $notificacion)
    {
        $this->authorize('delete', $notificacion);

        $notificacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada correctamente'
        ]);
    }
}
