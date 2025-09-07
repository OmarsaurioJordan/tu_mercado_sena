<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mensaje;

class MensajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $mensajes = Mensaje::all();
        return response()->json($mensajes, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $mensaje = Mensaje::create($request->all());
        return response()->json($mensaje, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $mensaje = Mensaje::find($id);
        if(!$mensaje){
            return response()->json([
                'message' => 'Mensaje no encontrado'
            ], 404);
        }
        return response()->json($mensaje, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $mensaje = Mensaje::find($id);
        if(!$mensaje){
            return response()->json([
                'message' => 'Mensaje no encontrado'
            ], 404);
        }
        $mensaje->update($request->only([
            'es_comprador',
            'chat_id',
            'mensaje',
            'es_imagen'
        ]));
        return response()->json($mensaje, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $mensaje = Mensaje::find($id);
        if(!$mensaje){
            return response()->json([
                'message' => 'Mensaje no encontrado'
            ], 404);
        }
        $mensaje->delete();
        return response()->json([
            'message' => 'Mensaje eliminado'
        ], 200);
    }
}
