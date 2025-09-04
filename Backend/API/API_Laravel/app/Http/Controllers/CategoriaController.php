<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $categorias = Categoria::all();
        if(!$categorias){
            return response()->json([
                'message' => 'No se encontraron categorias'
            ], 404);
        }
        return response()->json($categorias, 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $categoria = Categoria::create($request->all());
        if(!$categoria){
            return response()->json([
                'message' => 'Error al crear la categoria'
            ], 500);
        }

        return response()->json([
            'message' => 'Categoria creada exitosamente',
            'data' => $categoria
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json([
                'message' => 'Categoria no encontrada'
            ], 404);
        }
        return response()->json($categoria, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json([
                'message' => 'Categoria no encontrada'
            ], 404);
        }
        $categoria->update($request->all());
        return response()->json([
            'message' => 'Categoria actualizada correctamente',
            'categoria' => $categoria
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json([
                'message' => 'Categoria no encontrada'
            ], 404);
        }
        $categoria->delete();
        return response()->json([
            'message' => 'Categoria eliminada'
        ], 200);
    }
}
