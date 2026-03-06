<?php

namespace App\Http\Controllers\Api;

use App\Models\Motivo;
use Illuminate\Http\Request;

class MotivoController extends Controller
{
    /**
     * Obtener motivos filtrados por tipo.
     * 
     * GET /api/motivos?tipo=denuncia
     * GET /api/motivos?tipo=pqrs
     * GET /api/motivos?tipo=notificacion
     */
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');

        $query = Motivo::query();

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $motivos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $motivos,
        ]);
    }
}
