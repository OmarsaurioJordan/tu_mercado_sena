<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\Integridad;
use Illuminate\Http\JsonResponse;

class IntegridadController extends Controller
{
    /**
     * Listar todas las integridades
     * GET /api/integridad
     */
    public function index(): JsonResponse
    {
        try {
            $integridades = Integridad::all();
            return response()->json($integridades, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener integridades.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
