<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retornar usuarios por paginación de 20 en 20
        $usuarios = Usuario::paginate(20);
        return response()->json($usuarios, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $usuario = Usuario::create($request->all());
        return response()->json($usuario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $usuario = Usuario::find($id);
        if(!$usuario){
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        return response()->json($usuario, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $usuario = Usuario::find($id);
        if(!$usuario){
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        $usuario->update($request->only([
            'correo_id',
            'password',
            'rol_id',
            'nombre',
            'avatar',
            'descripcion',
            'link',
            'estado_id',
            'notifica_correo',
            'notifica_push',
            'uso_datos'
        ])); // Usar método PATCH para actualizar los campos según el rol del usuario(Admin, Usuario)
        return response()->json($usuario, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $usuario = Usuario::find($id);
        if(!$usuario){
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        $usuario->delete();
        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }
}
