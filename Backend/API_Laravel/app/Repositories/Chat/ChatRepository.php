<?php

namespace App\Repositories\Chat;

use App\Contracts\Chat\Repositories\IChatRepository;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChatRepository implements IChatRepository
{

    public function verificarBloqueoMutuo(Chat $chat): bool
    {
        $comprador = $chat->comprador;

        $vendedor = $chat->producto->vendedor;

        // Verificar si el comprador tiene bloqueado al vendedor
        $compradorBloqueoAVendedor = $comprador->usuariosQueHeBloqueado()
            ->where('bloqueado_id', $vendedor->id)
            ->exists();

        // Verificar si el vendedor tiene bloqueado al comprador
        $vendedorBloqueoAComprador = $vendedor->usuariosQueHeBloqueado()
            ->where('bloqueado_id', $comprador->id)
            ->exists();
        
        // Retornar true si cualquier de los 2 ha bloqueado al otro
        return $compradorBloqueoAVendedor || $vendedorBloqueoAComprador;
    }

    public function obtenerMapaDeBloqueos(Collection $chats, int $usuario_id): array
    {
        $ids_involucrados = $chats->map(function ($chat) use ($usuario_id) {
            return ($chat->comprador_id === $usuario_id)
                ? $chat->producto->vendedor_id
                : $chat->comprador_id;
        })->unique()->toArray();

        $bloqueos = DB::table('bloqueados')
            ->where(function($q) use ($usuario_id, $ids_involucrados) {
                $q->where('bloqueador_id', $usuario_id)
                  ->whereIn('bloqueado_id', $ids_involucrados);
            })
            ->orWhere(function($q) use ($usuario_id, $ids_involucrados) {
                $q->whereIn('bloqueador_id', $ids_involucrados)
                  ->where('bloqueado_id', $usuario_id);
            })
            ->get();

            return $bloqueos->map(fn($b) => $b->bloqueador_id == $usuario_id ? $b->bloqueado_id : $b->bloqueador_id)
                            ->unique()
                            ->toArray();
    }

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
                'producto: id, nombre, precio, vendedor_id',
                'producto.vendedor: id, nickname, imagen',
                'producto.fotos: id, producto_id, imagen',
                'estado: id, nombre',
                'ultimoMensaje'
        ])
        ->where('comprador_id', $usuario_id)
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
