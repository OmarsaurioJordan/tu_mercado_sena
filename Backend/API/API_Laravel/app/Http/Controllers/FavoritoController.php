<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorito;

class FavoritoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $favoritos = Favorito::all();
        return response()->json($favoritos, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $favorito = Favorito::create($request->all());
        return response()->json([
            'message' => 'Favorito agregado correctamente',
            'favorito' => $favorito
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $favorito = Favorito::find($id);
        if(!$favorito){
            return response()->json([
                'message' => 'Favorito no encontrado'
            ], 404);
        }
        return response()->json($favorito, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $favorito = Favorito::find($id);
        if(!$favorito){
            return response()->json([
                'message' => 'Favorito no encontrado'
            ], 404);
        }
        $favorito->delete();
        return response()->json([
            'message' => 'Favorito borrado de la lista correctamente'
        ], 200);
    }
}
