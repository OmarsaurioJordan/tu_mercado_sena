<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $productos = Producto::paginate(10);
        return response()->json($productos, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $producto = Producto::create($request->all());
        return response()->json([
            'message' => 'Producto creado correctamente',
            'producto' => $producto
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $producto = Producto::find($id);
        if(!$producto){
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }
        return response()->json($producto, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $producto = Producto::find($id);
        if(!$producto){
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }
        $producto->update($request->only([
            'nombre',
            'con_imagen',
            'subcategoria_id',
            'integridad_id',
            'vendedor_id',
            'estado_id',
            'descripcion',
            'precio',
            'disponibles'
        ]));
        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'producto' => $producto
        ], 200); // Usar método PATCH no actualizar todos los campos
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $producto = Producto::find($id);
        if(!$producto){
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }
        $producto->delete();
        return response()->json([
            'message' => 'Producto eliminado correctamente'
        ], 200);
    }
}
