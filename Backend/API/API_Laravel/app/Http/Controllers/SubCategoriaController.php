<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCategoria;

class SubCategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $subCategorias = SubCategoria::all();
        return response()->json($subCategorias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $subCategoria = SubCategoria::create($request->all());
        if(!$subCategoria) {
            return response()->json(['message' => 'Error al crear la subcategoria'], 500);
        }
        return response()->json($subCategoria);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $subCategoria = SubCategoria::find($id);
        if (!$subCategoria) {
            return response()->json(['message' => 'Subcategoria no encontrada'], 404);
        }
        return response()->json($subCategoria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $subCategoria = SubCategoria::find($id);
        if (!$subCategoria) {
            return response()->json(['message' => 'Subcategoria no encontrada'], 404);
        }
        $subCategoria->update($request->all());
        return response()->json($subCategoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $subCategoria = SubCategoria::find($id);
        if (!$subCategoria) {
            return response()->json(['message' => 'Subcategoria no encontrada'], 404);
        }
        $subCategoria->delete();
        return response()->json(['message' => 'Subcategoria eliminada con éxito']);
    }
}
