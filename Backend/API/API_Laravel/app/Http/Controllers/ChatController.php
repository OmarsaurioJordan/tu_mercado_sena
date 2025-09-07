<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $chats = Chat::paginate(10);
        return response()->json($chats, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $chat = Chat::create($request->all());
        return response()->json($chat, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $chat = Chat::find($id);
        if(!$chat){
            return response()->json([
                'message' => 'Chat no encontrado'
            ], 404);
        }
        return response()->json($chat, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $chat = Chat::find($id);
        if(!$chat){
            return response()->json([
                'message' => 'Chat no encontrado'
            ], 404);
        }
        $chat->update($request->only([
            'comprador_id',
            'producto_id',
            'estado_id',
            'visto_comprador',
            'visto_vendedor',
            'precio',
            'cantidad',
            'calificacion',
            'comentario'
        ]));
        return response()->json($chat, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $chat = Chat::find($id);
        if(!$chat){
            return response()->json([
                'message' => 'Chat no encontrado'
            ], 404);
        }
        $chat->delete();
        return response()->json([
            'message' => 'Chat eliminado correctamente'
        ], 200);
    }
}
