<?php

namespace App\Repositories\Chat;

use App\Contracts\Chat\Repositories\IChatRepository;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository implements IChatRepository
{
    public function create(array $datos): Chat
    {
        // Asignar por defecto el estado del chat
        if (!isset($data['estado_id']) || $data['estado_id'] === null) {
            $data['estado_id'] = 1;
        }

        // Crear el registro en la Base de datos
        $chat = Chat::create($datos);

        // Obtener los datos de las tablas relacionadas
        $chat->load([
            'comprador' => function ($query) {
                $query->select('id', 'nombre', 'imagen');
            },
            'producto' => function ($query) {
                $query->select('id', 'nombre', 'precio');
            },
            'producto.fotoPrincipal' => function ($query) {
                $query->select('id', 'producto_id', 'imagen')->first();
            },
            'estado' => function ($query) {
                $query->select('id', 'nombre');
            }
        ]);

        return $chat;
    }

    public function findModel(array $criterios): ?Chat
    {
        return Chat::where($criterios)
            ->with([
                'comprador: id, nombre, imagen',
                'producto: id, nombre, precio',
                'producto.fotos: id, producto_id, imagen',
                'estado: id, nombre'
            ])
            ->first();
    }

    public function listarChats(int $usuario_id): Collection
    {
        return Chat::with([
                'comprador: id, nombre, imagen',
                'producto: id, nombre, precio',
                'producto.fotos: id, producto_id, imagen',
                'estado: id, nombre',
                'ultimoMensaje'
        ])
        ->get();
    }

    public function delete(int $id): bool
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return false;
        }

        return $chat->delete();
    }

    public function findDetails(int $chatId): ?Chat
    {
        return Chat::with([
            'estado',
            'comprador:id,nombre,imagen',
            'producto.vendedor:id,nickname,imagen',
            'producto.fotos:id,producto_id,imagen', // Necesario para la foto del producto
            'mensajes' => function ($query) {
                $query->orderBy('created_at', 'asc'); // O 'fecha_registro' si usas nombres personalizados
            }
        ])->find($chatId);
    }
}
