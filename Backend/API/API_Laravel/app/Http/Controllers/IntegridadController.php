<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Integridad;

class IntegridadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $integridad = Integridad::all();
        if(!$integridad){
            return response()->json([
                'message' => 'No se encontraron integridades'
            ], 404);
        }
        return response()->json($integridad, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $integridad = Integridad::create($request->all());
        if(!$integridad){
            return response()->json([
                'message' => 'Error al crear la integridad'
            ], 500);
        }
        
        return response()->json([
            'message' => 'Integridad creada exitosamente',
            'data' => $integridad
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $integridad = Integridad::find($id);
        if(!$integridad){
            return response()->json([
                'message' => 'Integridad no encontrada'
            ], 404);
        }
        return response()->json($integridad, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $integridad = Integridad::find($id);
        if(!$integridad){
            return response()->json([
                'message' => 'Integridad no encontrada'
            ], 404);
        }
        $integridad->update($request->all());
        return response()->json([
            'message' => 'Integridad actualizada correctamente',
            'data' => $integridad
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $integridad = Integridad::find($id);
        if(!$integridad){
            return response()->json([
                'message' => 'Integridad no encontrada'
            ], 404);
        }
        $integridad->delete();
        return response()->json([
            'message' => 'Integridad eliminada correctamente'
        ], 200);
    }
}
